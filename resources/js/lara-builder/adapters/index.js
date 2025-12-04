/**
 * Output Adapters Module
 *
 * Exports all adapters and registers them with the OutputAdapterRegistry.
 */

export { BaseAdapter } from './BaseAdapter';
export { EmailAdapter } from './EmailAdapter';
export { WebAdapter } from './WebAdapter';

// Auto-register adapters with the registry
import { OutputAdapterRegistry } from '../registry/OutputAdapterRegistry';
import { EmailAdapter } from './EmailAdapter';
import { WebAdapter } from './WebAdapter';

// Register default adapters
OutputAdapterRegistry.register('email', new EmailAdapter());
OutputAdapterRegistry.register('page', new WebAdapter());
OutputAdapterRegistry.register('campaign', new EmailAdapter()); // Reuse email adapter for campaigns
