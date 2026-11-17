/**
 * What the signed-in user may do in the builder, as rendered by the server on #lara-builder-root.
 * The server enforces every permission itself; this only hides controls the user can't use.
 */
const rootData = () => document.getElementById("lara-builder-root")?.dataset ?? {};

/** AI writing tools (ai_content.generate). */
export const canUseAI = () => rootData().canUseAi !== "false";
