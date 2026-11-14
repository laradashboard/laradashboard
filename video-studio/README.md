# LaraDashboard Video Studio

A motion-design kit for LaraDashboard product videos, covering release, feature, tutorial and overview videos. It is built with Remotion 4, Playwright and ffmpeg. The Claude skill that drives it is at `.claude/skills/product-video/`.

```bash
cd video-studio && npm install --ignore-scripts      # once
node scripts/record.mjs crm-overview                 # record clips + interaction logs (app on :8000)
node scripts/qa.mjs crm-overview                     # contact sheet + motion warnings
node scripts/render.mjs crm-overview                 # -> out/crm-overview.mp4 (auto-repairs glitch frames)
node scripts/smoothness.mjs crm-overview             # flicker / stutter / jolt report
npm run studio                                       # Remotion Studio (live preview, placeholder props)
```

- `projects/<name>/storyboard.json` holds the scenes, text, VO lines, transitions and music. Its schema is in `src/types.ts`.
- `projects/<name>/scenes.mjs` is the recording script (recorder API is in `scripts/record.mjs`).
- `scripts/ingest.mjs` brings in your own screen recording and turns a marks file into camera events.
- `scripts/voiceover.mjs` creates ElevenLabs voice clips per scene. Copy `.env.example` to `.env` (gitignored) and set the key and voice ID.
- Music: `"music": "@library/digital-technology.mp3"` uses the shared library in `assets/music/` (licences in `LICENSES.md`). Voice and music are loudness-normalised and the music ducks under the voice automatically.

Rendering uses the system Google Chrome (`REMOTION_BROWSER` overrides it), so no browser download is needed.
Licence note: Remotion is free for individuals and companies of up to 3 people. Larger companies need a company licence.
