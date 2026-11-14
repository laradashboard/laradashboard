# Motion rules

What the user means by "proper motion, proper animation, proper zoom in/out", and how the kit delivers it.

## Camera
- **Every click gets a zoom.** The auto-camera arrives about 0.75s before the action, holds about 1.1s after it, then pulls back. Clicks that land close together pan instead of zooming out and in again.
- Zoom levels: 1.4–1.6 for a panel or card, 1.8–2.0 for a button or field. Never above 2.2. Big regions (a whole board column) cap themselves automatically.
- Zoom out to 1.0 for scrolls, page changes and "here's the whole page" moments.
- A slow drift (`drift`, default +3.5% over the scene) means even holds keep moving.
- Easing is bezier(0.65, 0, 0.25, 1), like a real camera operator. Zoom is interpolated in log space. Don't use linear moves.
- Manual override: set `"camera": [{"t": 2.1, "zoom": 1.7, "x": 820, "y": 370, "dur": 0.9}, …]` in clip time and clip CSS px. Use it only when the auto-camera picks wrong.
- In `full` layout the whole browser window zooms and the backdrop disappears. In split layouts the window stays put and the zoom happens inside it, so the headline is never covered.

## Cursor
- With recorder footage, the cursor is synthetic: smooth eased paths, a press squish and a brand-colour ripple on click. Its scale is moderated by 1/√zoom.
- Mouse moves take 400–900ms. Fast enough to feel confident, slow enough to follow. Pause 150–300ms before each click.

## Text
- Headlines animate in word by word with a spring, rising, un-blurring and fading in, staggered 3 frames per word. Colour 1–2 key words with `*word*`.
- Max about 7 words per headline and about 14 words of body. If the VO already says it, the on-screen text is the short version.
- The chapter chip (top-left) springs in whenever the chapter changes. The step tracker (top-centre) shows in tutorials.
- Captions (`caption`) are for the single most important action in a shot. Don't use them as subtitles for everything.

## Cards and diagrams
- Feature cards stagger in (5 frames apart), then a soft spotlight walks across them so the card scene isn't static.
- Diagram nodes pop in left to right. Connectors draw on, then carry a travelling pulse. A green check pops on the `check` node at 60%.
- Title and end cards float gently (sine drift), and the backdrop blobs drift constantly.

## Transitions
- Default `fade` (0.6s). Use `zoom` (camera push: the outgoing shot scales up and blurs, the incoming one settles in) to enter or leave title and feature cards. Avoid `slide`: whole-frame motion reads as a jolt. Every transition uses an ease-in-out curve, never linear.
- Don't hard-cut between two screen shots of the same page.

## Smoothness
- Overlays (highlight dims, outlines) fade in over ~0.5s with a soft-start curve — no one-frame pops.
- In-page UI changes in the footage should animate too (e.g. a drag ghost lifts over 0.35s instead of appearing).
- Gate every render with `scripts/smoothness.mjs`.

## Rhythm
- Overview and promo: 5–8s per screen scene. Tutorials can run longer, but each scene needs a beat (click, focus, highlight, scroll) at least every 2–3s.
- Alternate visual density: dense app screen → clean card → app screen with split headline → diagram…
- `qa.mjs` warns about screen shots longer than 3s with no camera beats, shots longer than 9s with fewer than 2 beats, and cards longer than 7s.
