import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import collectModuleAssetsPaths from "./vite-module-loader";

const paths = ["resources/css/app.css", "resources/js/app.js"];

// Use top-level await to properly load module assets
let allPaths = await collectModuleAssetsPaths(paths, "Modules");

if (allPaths.length === 0) {
    allPaths = paths;
}

export default defineConfig({
    plugins: [
        laravel({
            input: allPaths,
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: "automatic",
        // drop: ['console', 'debugger'],
    },
});
