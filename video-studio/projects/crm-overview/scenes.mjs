// Recording script for "LaraDashboard CRM in 60 seconds".
// Run: node scripts/record.mjs crm-overview [scene ...]   (app on http://localhost:8000, demo data seeded)
// Never click AI buttons (Analyze, AI Suggest, Weekly Digest) - they call OpenAI.

export const config = {
  baseUrl: 'http://localhost:8000',
  viewport: { width: 1440, height: 900 },
  scale: +(process.env.REC_SCALE ?? 1.5),
  async login(page, base) {
    page.setDefaultTimeout(90000);
    await page.goto(base + '/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"], input[name="login"], input[placeholder*="username" i]', process.env.DEMO_USER ?? 'superadmin');
    await page.fill('input[type="password"]', process.env.DEMO_PASS ?? '12345678');
    await Promise.all([page.waitForURL((u) => !u.pathname.startsWith('/login')), page.keyboard.press('Enter')]);
  },
};

const TICKET_ID = process.env.TICKET_ID ?? 11;
const CAMPAIGN_UUID = process.env.CAMPAIGN_UUID ?? 'b8525893-f665-44ba-ae34-95c2c259c1fa';
const WORKFLOW_UUID = process.env.WORKFLOW_UUID ?? 'e6591020-14f1-4a43-9094-982f61c96aaa';

export const scenes = {
  async dashboard(r) {
    await r.goto('/admin/crm/dashboard');
    await r.wait(500);
    await r.hover('h1', { hold: 0.9 });
    await r.hover('a:has-text("Create Deal")', { hold: 0.5 });
    await r.hover('a:has-text("New Ticket")', { ms: 450, hold: 0.5 });
    await r.hover('a:has-text("Create Invoice")', { ms: 450, hold: 0.8 });
    await r.move({ x: 860, y: 520 }, { ms: 700 });
    await r.scroll(380, { ms: 1600 });
    await r.wait(500);
    await r.focus({ x: 314, y: 140, w: 1100, h: 420 }, { zoom: 1.35, hold: 2 });
  },

  async contacts(r) {
    await r.goto('/admin/crm/contacts', { wait: 'table tbody tr' });
    await r.wait(400);
    const rows = r.page.locator('table tbody tr');
    await r.hover(rows.nth(0).locator('td').nth(1), { hold: 0.5 });
    await r.move(rows.nth(1).locator('td').nth(1), { ms: 380 });
    await r.move(rows.nth(2).locator('td').nth(1), { ms: 380 });
    await r.wait(300);
    await r.highlight('button:has-text("Type")', { ms: 1500, label: 'Filter by type, status and group' });
    await r.hover('a:has-text("New Contact")', { hold: 1.1 });
  },

  async deals(r) {
    await r.goto('/admin/crm/deals?view=board', { wait: '[data-stage-id]' });
    await r.wait(400);
    const card = r.page.locator('[data-deal-id]').filter({ hasText: 'Website Redesign' }).first();
    const id = await card.getAttribute('data-deal-id');
    const fromStage = await card.evaluate((el) => el.closest('[data-stage-id]')?.dataset.stageId);
    const toStage = String(Number(fromStage) + 1);
    // Each take moves the deal one stage. Reset before re-recording:
    //   php artisan tinker --execute="\Modules\Crm\Models\Deal::whereKey(15)->update(['stage_id'=>2]);"
    if (!(await r.page.locator(`[data-stage-id="${toStage}"]`).count())) throw new Error(`"Website Redesign" is already in the last stage (${fromStage}); reset it first`);
    await r.click(card, { pause: 150, zoom: 1.5 });
    const dest = await r.page.locator(`[data-stage-id="${toStage}"]`).evaluate((el) => {
      const cards = el.querySelectorAll('[data-deal-id]'); const last = cards[cards.length - 1];
      const b = (last || el).getBoundingClientRect(); return { x: b.left + b.width / 2, y: Math.min(window.innerHeight - 90, b.bottom + 60) };
    });
    // HTML5 drag ghosts are not captured by the screencast, so a lifted clone follows the mouse.
    await r.page.evaluate(({ id }) => {
      const src = document.querySelector(`[data-deal-id="${id}"]`), b = src.getBoundingClientRect();
      const g = src.cloneNode(true); g.id = 'rec-ghost'; g.removeAttribute('data-deal-id');
      Object.assign(g.style, { position: 'fixed', left: b.left + 'px', top: b.top + 'px', width: b.width + 'px', zIndex: 2147483640, margin: 0, transition: 'transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s',
        transform: 'none', boxShadow: '0 0 0 rgba(20,10,80,0)', pointerEvents: 'none', background: '#fff', borderRadius: '12px' });
      // Lift on the next frame so the change animates instead of popping.
      requestAnimationFrame(() => requestAnimationFrame(() => { g.style.transform = 'rotate(3deg) scale(1.04)'; g.style.boxShadow = '0 24px 48px rgba(20,10,80,.28)'; }));
      const cx = b.left + b.width / 2, cy = b.top + b.height / 2;
      src.style.transition = 'opacity .3s'; src.style.opacity = '.3'; document.body.appendChild(g);
      document.addEventListener('mousemove', (window.__ghost = (e) => { g.style.left = (e.clientX - cx + b.left) + 'px'; g.style.top = (e.clientY - cy + b.top) + 'px'; }), true);
    }, { id });
    await r.wait(250);
    await r.move(dest, { ms: 1400, stepped: true });
    await r.wait(150);
    await r.page.evaluate(({ id, toStage }) => {
      document.removeEventListener('mousemove', window.__ghost, true); document.getElementById('rec-ghost')?.remove();
      const card = document.querySelector(`[data-deal-id="${id}"]`), target = document.querySelector(`[data-stage-id="${toStage}"]`);
      card.style.opacity = '';
      const o = { dataTransfer: new DataTransfer(), bubbles: true, cancelable: true };
      card.dispatchEvent(new DragEvent('dragstart', o)); target.dispatchEvent(new DragEvent('dragover', o));
      target.dispatchEvent(new DragEvent('drop', o)); card.dispatchEvent(new DragEvent('dragend', o));
    }, { id, toStage });
    await r.wait(500);
    await r.focus(`[data-stage-id="${toStage}"]`, { zoom: 1.45, hold: 1.8 });
  },

  async ticket(r) {
    await r.goto(`/admin/crm/tickets/${TICKET_ID}`, { wait: 'text=corrected invoice' });
    await r.wait(500);
    await r.hover('text=Invoice shows wrong tax', { hold: 0.8 });
    await r.move({ x: 760, y: 520 }, { ms: 700 });
    await r.scroll(320, { ms: 1500 });
    await r.wait(300);
    await r.focus(r.page.getByText('corrected invoice', { exact: false }).first(), { zoom: 1.7, hold: 1.8 });
  },

  async workflow(r) {
    await r.goto(`/admin/crm/workflows/${WORKFLOW_UUID}/edit`, { wait: '.react-flow__node' });
    await r.wait(800);
    const nodes = r.page.locator('.react-flow__node');
    const n = await nodes.count();
    await r.hover(nodes.nth(0), { hold: 0.7, zoom: 1.6 });
    for (let i = 1; i < Math.min(n, 4); i++) await r.hover(nodes.nth(i), { ms: 600, hold: 0.6, zoom: 1.6 });
    await r.move({ x: 1180, y: 300 }, { ms: 600 });
    await r.wait(1200);
  },

  async analytics(r) {
    await r.goto(`/admin/crm/email-campaigns/${CAMPAIGN_UUID}/analytics`, { wait: 'text=Engagement Funnel' });
    await r.wait(500);
    await r.move({ x: 900, y: 360 }, { ms: 600 });
    await r.scroll(420, { ms: 1500 });
    await r.wait(300);
    await r.focus(r.page.locator('text=Engagement Funnel').locator('xpath=ancestor::*[contains(@class,"card") or contains(@class,"rounded")][1]'), { zoom: 1.5, hold: 2 });
  },

  async reports(r) {
    await r.goto('/admin/crm/reports');
    await r.wait(500);
    await r.click('button:has-text("Lead Funnel")', { pause: 1200, zoom: 1.6 });
    await r.move({ x: 860, y: 560 }, { ms: 700 });
    await r.wait(1500);
  },
};
