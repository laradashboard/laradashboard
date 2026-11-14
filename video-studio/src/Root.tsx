import React from 'react';
import { Composition } from 'remotion';
import { ProductVideo, timeline } from './Video';
import type { Storyboard } from './types';

const placeholder: Storyboard = {
  title: 'Preview',
  category: 'feature',
  scenes: [
    { type: 'title', duration: 3, title: 'Run your whole business from *one dashboard*', chips: ['Contacts', 'Deals', 'Helpdesk'] },
    { type: 'end', duration: 3, title: 'Where to go next', cards: [{ icon: 'book', title: 'Docs', lines: ['Read the guide'], cta: 'Read the guide' }] },
  ],
};

export const Root: React.FC = () => (
  <Composition
    id="Product"
    component={ProductVideo as unknown as React.FC<Record<string, unknown>>}
    defaultProps={placeholder as unknown as Record<string, unknown>}
    durationInFrames={300}
    fps={30}
    width={1920}
    height={1080}
    calculateMetadata={({ props }) => {
      const sb = props as unknown as Storyboard;
      return { durationInFrames: timeline(sb).total, fps: sb.fps ?? 30, width: sb.width ?? 1920, height: sb.height ?? 1080 };
    }}
  />
);

