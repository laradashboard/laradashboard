---
name: product-video
description: Make polished, motion-designed product videos for LaraDashboard and its modules (CRM, etc.) - release videos, feature videos, tutorials/fixes and overviews - with camera zooms keyed to clicks, a branded stage, kinetic text, animated explainer cards, ElevenLabs voiceover and music. Use whenever the user asks for a product/feature/release/tutorial/demo/promo video, wants to edit a screen recording they made, or mentions the video plan in crm-video-plan/. Never produce a plain screen capture with captions.
---

# Product video

Everything is built with the kit in `video-studio/` (Remotion 4 + Playwright + ffmpeg). One folder per video in `video-studio/projects/<name>/` holding `storyboard.json`, `clips/`, optional `scenes.mjs` (recording script), `music.mp3`, `vo/`.

**Quality bar:** the user rejects static edits outright. Each shot needs a camera move, a cursor action or text animating in, and nothing may sit still for more than about 2 seconds. The references are SureCart's YouTube videos: a 4.12 release, a "33 languages" tutorial and a "store disconnected" fix. Match them or beat them. See `references/motion-rules.md`.

## Workflow

1. **Pick the category and write the storyboard first.** Use `references/categories.md` (Release / Feature / Tutorial / Overview beat sheets). Write the VO line per scene before recording, so each shot knows how long it must be. Show the user the beat list (one line per scene) before building anything large.
2. **Get footage.** There are two options:
   - **Record it yourself (preferred, perfect camera data).** Write `projects/<name>/scenes.mjs` using the recorder API in `references/recording.md`, then run `node scripts/record.mjs <name> [scene…]` with the app on :8000. It outputs `clips/<scene>.mp4` at 1.5× DPR, exactly 30 fps, with no cursor burned in, plus `clips/<scene>.events.json` (moves, clicks, focus, highlights, scrolls) that drives the auto-camera and the synthetic cursor.
   - **User's own recording.** Run `node scripts/ingest.mjs <name> <file>`. It transcodes the file and writes timestamped thumbnails every 0.5s. Read the contact sheets, write `marks.txt` (clicks, focus regions, highlights), then run `node scripts/ingest.mjs <name> --marks=marks.txt`. In the storyboard, split the long recording into several `screen` scenes with `trim` windows and set `"cursor": false`, because the real cursor is already in the footage. Details are in `references/recording.md`.
3. **Fill in `storyboard.json`.** The schema is `video-studio/src/types.ts` and the full example is `projects/crm-overview/storyboard.json`. Mix scene types: `title`, `screen` (layout `full` / `split-left` / `split-right` with a headline), `features`, `diagram`, `statement`, `end`. Never put more than two `screen` scenes in a row without a card or split headline between them.
4. **Voice (optional).** Run `node scripts/voiceover.mjs <name>`. It reads `ELEVENLABS_API_KEY`, `ELEVENLABS_VOICE_ID` and `ELEVENLABS_MODEL` from `video-studio/.env` (gitignored; template in `.env.example`) or from the shell. It generates one clip per scene from `vo`, caches by text hash, and writes `voFile`/`voDuration`. Scenes stretch to fit the voice plus 0.9s, footage holds its last frame if the voice outlasts it, and music ducks under the voice automatically. Quote the character count, and never print the key.
5. **QA before showing anything.** Run `node scripts/qa.mjs <name>`. It renders 3 frames per scene into `projects/<name>/qa/sheet.jpg` and prints motion warnings. **Open the sheet and look at it.** Check for: text overlapping the window, zoom landing on the wrong element, empty or blank frames, cut-off text, an unreadable workflow or table (zoom more), and scenes that look the same at start/mid/end (add beats). Fix the problems and re-run.
6. **Render.** Run `node scripts/render.mjs <name>` → `video-studio/out/<name>.mp4` (1080p, CRF 16). Use `--preview` for half-res quick checks, and `--from=12 --to=20` to re-render a slice. A 60s video takes about 5–10 minutes. The render then repairs **render glitches** automatically: frames occasionally captured before the page painted (white or half-blank) get re-rendered as stills and patched in (`scripts/repair.mjs`; skip with `--no-repair`).
7. **Smoothness gate.** Run `node scripts/smoothness.mjs <name>`. It measures exact frame-to-frame change and reports **flicker** (one-frame pops), **stutter** (frozen frames during motion) and **jolts** (motion starting or stopping at full speed). Fix anything that comes from the kit or the footage. Instant UI changes inside the app (a tab switch, a toast) are fine, but check those by eye. The user notices flicker straight away.
8. Send the user the MP4 (SendUserFile) plus the contact sheet. Files over 30 MB only reach the desktop app, so say so. Say what isn't done yet (no music file, etc.).

## Rules

- **Never click AI features while recording** (CRM "Analyze", "AI Suggest Reply", "Weekly Digest", AI agents' test runs). They call the user's OpenAI key. Check that `grep -c "AI Content\|openai\|OpenAI" storage/logs/laravel.log` doesn't change.
- Seed realistic demo data first (names, amounts, statuses). Empty tables or "No data" never appear on screen.
- Record at 1440×900 CSS with `scale: 1.5` (2160×1350). 2× halves capture fps; 1.5× keeps zooms sharp to ~1.8×.
- Anything that moves *in the page* (scroll, drag) must use stepped capture (`r.scroll`, `r.move(..., { stepped: true })`) — screenshot per frame on a virtual 30fps clock. Real-time screencast drops frames under load.
- Transitions: `fade` and `zoom` only by default; all transitions are eased in/out. Avoid `slide` (whole-frame motion reads as a jolt).
- Music: use the shared library `video-studio/assets/music/` via `"music": "@library/<file>.mp3"` (`digital-technology.mp3` is upbeat, for overviews and releases; `corporate-ambient.mp3` is calm, for tutorials). Licences are in `assets/music/LICENSES.md`. To add tracks, use Pixabay Music (or another source with a clear free-commercial licence). Check the track is **not "Content ID Registered"**, ask the user before downloading, and record it in LICENSES.md. Never rip audio from YouTube.
- Loudness is automatic: voice and music are normalised to −16 LUFS, music plays at 0.5 (about −22 LUFS) and ducks to about −31 LUFS under the voice.
- Keep the core/module boundary: the kit lives in `video-studio/` with its own `package.json`. Never add its dependencies to the root `package.json` or to a module.
- Remotion licence: free for individuals and companies of 3 or fewer people. Larger companies need a Remotion company licence.
- Brand: primary `#635bff`, product name in `brand.product`. Use `*word*` in titles to colour a word.

## Files

- `references/categories.md`: beat sheets and durations for Release / Feature / Tutorial / Overview.
- `references/motion-rules.md`: what "proper motion" means here, plus the camera, text and transition rules.
- `references/recording.md`: recorder API, `scenes.mjs` template, user-recording ingest and the marks format.
- `video-studio/src/`: components (`scenes/ScreenScene.tsx` camera stage, `scenes/CardScenes.tsx` cards, `Video.tsx` timeline, transitions, chip, step tracker, captions, music ducking, `lib/motion.ts` camera / auto-camera / cursor maths).
