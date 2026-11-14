import React, { createContext, useContext } from 'react';
import type { Brand } from '../types';

export const defaultBrand: Brand = {
  name: 'LaraDashboard',
  primary: '#635bff',
  primaryDark: '#4f46e5',
  ink: '#1e1b4b',
  url: 'laradashboard.com',
};

export const FONT = `-apple-system, BlinkMacSystemFont, "SF Pro Display", "Inter", "Helvetica Neue", Arial, sans-serif`;

const Ctx = createContext<Brand>(defaultBrand);
export const BrandProvider: React.FC<{ brand?: Partial<Brand>; children: React.ReactNode }> = ({ brand, children }) => (
  <Ctx.Provider value={{ ...defaultBrand, ...brand }}>{children}</Ctx.Provider>
);
export const useBrand = () => useContext(Ctx);

/** Built-in LaraDashboard mark (an "L" with a dot). */
export const Mark: React.FC<{ size?: number; color?: string; bg?: string }> = ({ size = 48, color = '#fff', bg }) => {
  const b = useBrand();
  return (
    <svg width={size} height={size} viewBox="0 0 48 48" aria-hidden>
      <rect width="48" height="48" rx="12" fill={bg ?? b.primary} />
      <path d="M15 13v22h19" stroke={color} strokeWidth="4.6" fill="none" strokeLinecap="round" strokeLinejoin="round" />
      <circle cx="31" cy="18" r="4.6" fill={color} />
    </svg>
  );
};

/** Renders "*word*" segments in the brand colour. */
export function splitEmphasis(text: string): { word: string; em: boolean }[] {
  const out: { word: string; em: boolean }[] = [];
  let em = false;
  for (const part of text.split(/(\*)/)) {
    if (part === '*') { em = !em; continue; }
    for (const w of part.split(/(\s+)/)) if (w.trim()) out.push({ word: w, em });
  }
  return out;
}
