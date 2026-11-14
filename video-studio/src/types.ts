// Storyboard schema. One JSON file per video (projects/<name>/storyboard.json).
// All file paths are relative to the project folder. Times are in seconds.
// Screen-scene coordinates (camera, cursor, highlights) are CSS pixels of the recorded viewport,
// and times inside a screen scene are CLIP times (the same clock as the recording / events file).

export type Brand = {
  name: string; // "LaraDashboard"
  product?: string; // "CRM"
  primary: string; // "#635bff"
  primaryDark: string; // "#4f46e5"
  ink: string; // headline colour on light backgrounds
  url?: string; // "laradashboard.com"
  logo?: string; // optional image path; falls back to the built-in mark
};

export type CamKey = { t: number; zoom: number; x?: number; y?: number; dur?: number };

export type RecEvent =
  | { type: 'move'; t0: number; t1: number; x0: number; y0: number; x: number; y: number }
  | { type: 'click'; t: number; x: number; y: number; box?: Box; zoom?: number | false; label?: string }
  | { type: 'focus'; t: number; box: Box; zoom?: number; hold?: number; label?: string }
  | { type: 'highlight'; t: number; dur: number; box: Box; label?: string }
  | { type: 'scroll'; t0: number; t1: number; dy: number }
  | { type: 'type'; t0: number; t1: number; box: Box }
  | { type: 'nav'; t: number; url: string };

export type Box = { x: number; y: number; w: number; h: number };

export type Highlight = { t: number; dur: number; box: Box; label?: string; spotlight?: boolean };

export type Headline = { kicker?: string; title: string; body?: string };

export type TransitionName = 'fade' | 'slide' | 'zoom' | 'none';

type SceneBase = {
  id?: string;
  chapter?: string; // text in the persistent top-left chip
  step?: { n: number; total: number; label?: string };
  caption?: string; // subtitle line at the bottom
  vo?: string; // voiceover text (scripts/voiceover.mjs turns it into voFile/voDuration)
  voFile?: string;
  voDuration?: number;
  transition?: TransitionName; // transition INTO the next scene
};

export type ScreenScene = SceneBase & {
  type: 'screen';
  src: string; // "clips/deals.mp4"
  size: [number, number]; // recorded viewport in CSS px, e.g. [1440, 900]
  trim?: [number, number]; // clip seconds; default whole clip (needs events.duration)
  rate?: number; // playback rate (0.5 - 2)
  url?: string; // text in the fake address bar
  layout?: 'full' | 'split-left' | 'split-right'; // split = headline beside the window
  headline?: Headline;
  camera?: CamKey[]; // manual camera; replaces auto-camera
  cameraMode?: 'auto' | 'manual' | 'static';
  maxZoom?: number; // auto-camera zoom on clicks (default 1.8)
  drift?: number; // slow push-in across the scene (default 0.035)
  events?: string; // "clips/deals.events.json" (written by scripts/record.mjs)
  cursor?: boolean; // draw the synthetic cursor from events (default true when events exist)
  highlights?: Highlight[];
  _events?: { size: [number, number]; duration: number; events: RecEvent[] }; // filled by resolve
};

export type TitleScene = SceneBase & {
  type: 'title';
  duration: number;
  kicker?: string;
  title: string; // use *word* to colour a word with the brand colour
  big?: string; // giant number/word, e.g. "1.6" for a release
  chips?: string[];
  hero?: { src: string; at?: number }[]; // up to 3 clips/images floating behind the title
  dark?: boolean;
};

export type StatementScene = SceneBase & { type: 'statement'; duration: number; text: string; sub?: string; dark?: boolean };

export type Feature = { icon: IconName; title: string; desc?: string };
export type FeaturesScene = SceneBase & { type: 'features'; duration: number; kicker?: string; title: string; items: Feature[] };

export type DiagramNode = { id: string; label: string; icon?: IconName; sub?: string };
export type DiagramScene = SceneBase & {
  type: 'diagram';
  duration: number;
  title?: string;
  nodes: DiagramNode[]; // laid out left to right in order
  check?: string; // id of a node that gets a green check at the end
};

export type EndCard = { icon?: IconName; title: string; lines?: string[]; cta?: string };
export type EndScene = SceneBase & { type: 'end'; duration: number; kicker?: string; title: string; cards?: EndCard[]; url?: string };

export type Scene = ScreenScene | TitleScene | StatementScene | FeaturesScene | DiagramScene | EndScene;

export type Storyboard = {
  title: string;
  category: 'release' | 'feature' | 'tutorial' | 'overview';
  fps?: number;
  width?: number;
  height?: number;
  brand?: Partial<Brand>;
  transition?: TransitionName; // default between scenes
  transitionDuration?: number; // seconds, default 0.6
  music?: string; // project file, or "@library/<file>" from assets/music (see LICENSES.md)
  musicVolume?: number; // default 0.5 of the -16 LUFS-normalised track (ducked to 35% under voice)
  scenes: Scene[];
};

export type IconName =
  | 'users' | 'kanban' | 'ticket' | 'mail' | 'workflow' | 'chart' | 'invoice' | 'sparkles'
  | 'check' | 'globe' | 'shield' | 'zap' | 'book' | 'message' | 'bell' | 'settings' | 'box' | 'play';
