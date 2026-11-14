import React from 'react';
import { AbsoluteFill, interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { FONT, splitEmphasis, useBrand } from '../lib/brand';
import { clamp } from '../lib/motion';

/** Soft branded backdrop with slowly drifting light blobs, so even "static" frames breathe. */
export const Backdrop: React.FC<{ dark?: boolean }> = ({ dark }) => {
  const f = useCurrentFrame();
  const b = useBrand();
  const a = f / 240;
  const blob = (x: number, y: number, r: number, c: string) =>
    `radial-gradient(${r}px ${r * 0.75}px at ${x}% ${y}%, ${c} 0%, transparent 70%)`;
  const bg = dark
    ? [blob(18 + 6 * Math.sin(a), 12 + 5 * Math.cos(a * 0.8), 1100, `${b.primary}cc`), blob(88 - 5 * Math.sin(a * 0.7), 95, 1000, '#2a1fbfcc'), `linear-gradient(160deg, #241c8f, #120d4d)`]
    : [blob(15 + 6 * Math.sin(a), 8 + 5 * Math.cos(a * 0.8), 1100, `${b.primary}33`), blob(92 - 6 * Math.sin(a * 0.7), 96, 1000, `${b.primary}26`), blob(60, 40 + 8 * Math.sin(a * 0.5), 900, '#ffffffaa'), 'linear-gradient(180deg,#fbfaff,#efedff)'];
  return (
    <AbsoluteFill style={{ background: bg.join(',') }}>
      <AbsoluteFill style={{ backgroundImage: `radial-gradient(${dark ? 'rgba(255,255,255,.07)' : 'rgba(30,27,75,.06)'} 1.2px, transparent 1.2px)`, backgroundSize: '28px 28px', maskImage: 'radial-gradient(ellipse at center, black 30%, transparent 75%)' }} />
    </AbsoluteFill>
  );
};

/** Word-by-word headline: each word rises, un-blurs and fades in on a spring. */
export const Kinetic: React.FC<{
  text: string; size: number; delay?: number; color?: string; weight?: number; align?: 'left' | 'center'; stagger?: number; lineHeight?: number; maxWidth?: number; exitAt?: number;
}> = ({ text, size, delay = 0, color, weight = 800, align = 'left', stagger = 3, lineHeight = 1.04, maxWidth, exitAt }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const words = splitEmphasis(text);
  return (
    <div style={{ fontFamily: FONT, fontSize: size, fontWeight: weight, letterSpacing: -size * 0.03, lineHeight, color: color ?? b.ink, textAlign: align, maxWidth, display: 'flex', flexWrap: 'wrap', justifyContent: align === 'center' ? 'center' : 'flex-start', columnGap: size * 0.26 }}>
      {words.map((w, i) => {
        const s = spring({ frame: f - delay - i * stagger, fps, config: { damping: 18, stiffness: 140, mass: 0.7 } });
        const out = exitAt !== undefined ? clamp((f - exitAt - i * 1.5) / 10) : 0;
        return (
          <span key={i} style={{ display: 'inline-block', color: w.em ? b.primary : undefined, opacity: s * (1 - out), transform: `translateY(${(1 - s) * size * 0.55 - out * size * 0.3}px)`, filter: `blur(${(1 - s) * 10 + out * 8}px)` }}>
            {w.word}
          </span>
        );
      })}
    </div>
  );
};

/** Fades + rises a block in. */
export const Rise: React.FC<{ delay?: number; children: React.ReactNode; style?: React.CSSProperties; distance?: number }> = ({ delay = 0, children, style, distance = 30 }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const s = spring({ frame: f - delay, fps, config: { damping: 20, stiffness: 120 } });
  return <div style={{ ...style, opacity: s, transform: `translateY(${(1 - s) * distance}px)` }}>{children}</div>;
};

export const Pill: React.FC<{ children: React.ReactNode; dark?: boolean; style?: React.CSSProperties }> = ({ children, dark, style }) => (
  <div style={{ fontFamily: FONT, display: 'inline-flex', alignItems: 'center', gap: 12, padding: '14px 26px', borderRadius: 999, fontSize: 28, fontWeight: 600,
    background: dark ? 'rgba(255,255,255,.14)' : '#fff', color: dark ? '#fff' : '#2b2a4a', boxShadow: dark ? 'inset 0 0 0 1px rgba(255,255,255,.18)' : '0 6px 22px rgba(40,30,120,.10), inset 0 0 0 1px rgba(40,30,120,.08)', ...style }}>
    {children}
  </div>
);

/** Number of frames since `start` mapped through a spring - helper for scene choreography. */
export const useSpringAt = (start: number, config = { damping: 18, stiffness: 120 }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  return spring({ frame: f - start, fps, config });
};

export const fadeOutNear = (frame: number, total: number, len = 12) => interpolate(frame, [total - len, total], [1, 0], { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' });
