#!/usr/bin/env node

const colors = {
    reset: "\x1b[0m",
    bold: "\x1b[1m",

    cyan: "\x1b[36m",
    green: "\x1b[32m",
    yellow: "\x1b[33m",
    gray: "\x1b[90m",
};

console.log("");

console.log(
    colors.cyan +
    colors.bold +
    "Focus CMS build system" +
    colors.reset
);

console.log("");

console.log(
    colors.gray +
    "Please choose a build target:" +
    colors.reset
);

console.log("");

console.log(
    "  " +
    colors.green +
    "npm run build:app" +
    colors.reset +
    colors.gray +
    "       Build core app assets" +
    colors.reset
);

console.log(
    "  " +
    colors.green +
    "npm run build:theme" +
    colors.reset +
    colors.gray +
    "     Build active theme assets" +
    colors.reset
);

console.log(
    "  " +
    colors.green +
    "npm run build:modules" +
    colors.reset +
    colors.gray +
    "   Build all module assets" +
    colors.reset
);

console.log("");

console.log(
    colors.yellow +
    "Example:" +
    colors.reset
);

console.log(
    "  " +
    colors.green +
    "npm run build:theme" +
    colors.reset
);

console.log("");