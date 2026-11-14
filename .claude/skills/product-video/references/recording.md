# Recording footage

## A. Record with the kit (preferred)

`video-studio/projects/<name>/scenes.mjs`:

```js
export const config = {
  baseUrl: 'http://localhost:8000',
  viewport: { width: 1440, height: 900 },
  scale: 1.5,                                // 1.5x DPR: sharp zooms, ~30fps capture
  async login(page, base) {                  // seeded local test user only
    await page.goto(base + '/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"], input[name="login"]', 'superadmin');
    await page.fill('input[type="password"]', '12345678');
    await Promise.all([page.waitForURL((u) => !u.pathname.startsWith('/login')), page.keyboard.press('Enter')]);
  },
};

export const scenes = {
  async contacts(r) {
    await r.goto('/admin/crm/contacts', { wait: 'table tbody tr' }); // recording starts once the page has settled
    await r.hover('table tbody tr >> nth=0', { hold: 0.6 });         // move + camera focus
    await r.highlight('button:has-text("Type")', { ms: 1500, label: 'Filter by type' });
    await r.click('a:has-text("New Contact")', { zoom: 1.8 });
    await r.type('input[name="first_name"]', 'Ava');
    await r.scroll(400, { ms: 1400 });                                // scrolls the panel under the cursor
  },
};
```

Run it with `node scripts/record.mjs <name> [scene ...]`.

Recorder API (`r`). A target can be a selector, a locator, `{x,y}` or `{x,y,w,h}`.

| call | what it logs / does |
|---|---|
| `goto(path, {wait})` | navigates, waits for the selector and network idle; the first call starts the clip |
| `move(t, {ms, stepped})` | eased mouse move (cursor path); `stepped: true` when the page reacts to the mouse (drag ghosts) |
| `click(t, {ms, zoom, label, pause})` | move + click; camera zooms (`zoom:false` disables) |
| `hover(t, {hold, zoom})` | move + camera focus without clicking |
| `focus(t, {zoom, hold})` | camera focus without moving the mouse |
| `highlight(t, {ms, label})` | spotlight + outline (+ label pill) |
| `type(t, text, {delay})` | click + typing; camera holds on the field |
| `scroll(dy, {ms})` | eased, stepped (one screenshot per frame) scroll of the panel under the cursor; camera zooms out |
| `drag(from, to)` | visible drag (HTML5 DnD apps need custom code; see deals in `projects/crm-overview/scenes.mjs`) |
| `wait(ms)`, `page`, `log(evt)` | raw access |

How capture works: CDP screencast for normal time (frames timestamped, resampled to exact 30fps by nearest frame), plus stepped screenshot capture for scrolls/drags on a virtual clock — so in-page motion is always one fresh frame per 1/30s. Don't use ffmpeg's concat demuxer for image sequences: it rounds to 25fps and duplicates every 6th frame.

Tips:
- One page per scene. Navigating mid-scene records the load gap, so make it a new scene instead.
- Use `page.locator(...).getAttribute()` to look up IDs (deal IDs, stages) instead of hardcoding them.
- Slow pages (tickets) need `goto(url, { wait: '<text that proves it loaded>' })`.
- Dev overlays, debugbar and toasts are hidden automatically.
- Check the raw clips with a quick contact sheet before building the storyboard (`ffmpeg -ss … -frames:v 1`).

## B. The user's own recording

Ask the user to record:
- a browser window at 100% zoom, as large as possible, with no OS zoom effects
- slow, deliberate mouse moves, with a pause of about 1s after each click
- no voice needed, since ElevenLabs voice is added later
- MP4 or MOV

Then:
1. `node scripts/ingest.mjs <name> ~/Downloads/rec.mov --name=raw` → `clips/raw.mp4` plus `qa/ingest-raw/sheet-*.jpg` (timestamped thumbnails every 0.5s, 1440px coordinate space).
2. Read the sheets and write `projects/<name>/marks.txt`:
   ```
   # time type args...           (x y in the 1440-wide thumbnail space)
   3.5  click 1270 117 150 40 New Contact
   6.0  focus 330 250 1080 300 1.6
   9.2  highlight 845 194 127 40 1.8 Filter by status
   12.0 scroll 1.5
   ```
   Open individual thumbnails (`qa/ingest-raw/00007.jpg`) for exact coordinates.
3. `node scripts/ingest.mjs <name> --marks=marks.txt --name=raw` → `clips/raw.events.json`.
4. In the storyboard, cut the long take into several `screen` scenes sharing `"src": "clips/raw.mp4"` with different `trim` windows (skip dead time and page loads). Set `"size"` to what ingest printed and `"cursor": false`. Use `rate: 1.25`–`1.5` to speed up slow typing.
