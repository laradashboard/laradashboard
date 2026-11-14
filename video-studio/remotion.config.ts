import { Config } from '@remotion/cli/config';

// Use the system Chrome so nothing extra is downloaded. Override with REMOTION_BROWSER.
Config.setBrowserExecutable(process.env.REMOTION_BROWSER ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome');
Config.setChromeMode('chrome-for-testing');
Config.setVideoImageFormat('jpeg');
Config.setJpegQuality(92);
Config.setCodec('h264');
Config.setCrf(16);
