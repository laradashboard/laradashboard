# Video categories: beat sheets

Speaking pace is about 2.5 words/second. A scene lasts its footage length, or its VO length + 0.7s, whichever is longer. Keep text cards to 3.5–6s.

## Release video (60–150s), e.g. "LaraDashboard 1.6" or "CRM 2.3"
Reference: SureCart 4.12. The version number is the hero.
1. `title` with `big: "1.6"`, brand line, 2–4 `chips` naming the headline features, and `hero` clips faintly behind. 3.5s, transition `zoom`.
2. For each headline feature (2–4 of them):
   - `statement` or kinetic split headline naming the benefit ("Save for later"). 2.5s.
   - 1–3 `screen` scenes showing it, with `chapter` set to the feature name (persistent chip top-left), at least one `split-left`/`split-right` with a headline, and zoom on every click.
   - Optional `diagram` if the feature is conceptual (sync, workflows, permissions).
3. `features` card: "Also in 1.6" with 4–6 smaller changes. 4–5s.
4. `title` with `big` again plus "Available now", or an `end` card with update instructions. 4s.

## Feature video (30–90s), one capability, e.g. "Deal pipelines"
1. `title` (no big number): problem → promise in the headline, 1–3 hero clips. 3.5s.
2. `diagram` or `statement` explaining the idea in one sentence. 3–4s.
3. 3–5 `screen` scenes doing the real task end to end. Alternate `full` and split layouts, and use `highlight`/`focus` on the key control.
4. `features` with 3–4 related abilities. 4s.
5. `end` with cards: Try it / Docs / related feature video.

## Tutorial / fix video (1–5 min), e.g. "Connect SMTP" or "Fix a disconnected store"
Reference: SureCart "33 languages" and "Store disconnected".
1. `title` stating the outcome ("Send CRM emails through your own SMTP"). 3s.
2. `diagram` of the before/after or how the pieces connect, with `check` on the fixed node. 4–5s.
3. Steps: each step gets `step: {n, total, label}` (animated tracker top-centre) and 1–3 `screen` scenes. Spotlight every field the viewer must touch (`r.highlight(...)` with a label).
4. `statement`: "That's it - <outcome>". 2.5s.
5. `end`: "Where to go next" with Docs (bullet the steps), Comment and Subscribe.

## Overview / promo (45–75s), e.g. "LaraDashboard CRM in 60 seconds"
Example: `video-studio/projects/crm-overview`.
1. `title` with hero clips. 4s.
2. One `screen` per module area (5–7 areas), 5–8s each, each with a `chapter`. Alternate full and split headline layouts and add a `caption` on the most important action.
3. Break up the screen run with a `features` grid and a `diagram`.
4. `end` card.
