import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import collectModuleAssetsPaths from "./vite-module-loader";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const paths = [
    "resources/css/app.css",
    "resources/js/app.js",
    "resources/js/email-builder/index.jsx",
    "resources/js/lara-builder/entry.jsx",
    "resources/js/lara-builder/post-entry.jsx",
];

// Use top-level await to properly load module assets
let allPaths = await collectModuleAssetsPaths(paths, "modules");

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
    resolve: {
        alias: {
            react: path.resolve(__dirname, "node_modules/react"),
            "react-dom": path.resolve(__dirname, "node_modules/react-dom"),
        },
        dedupe: ["react", "react-dom"],
    },
    optimizeDeps: {
        include: ["react", "react-dom", "@dnd-kit/core", "@dnd-kit/sortable", "@dnd-kit/utilities"],
    },
});
