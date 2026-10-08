import { readFile, writeFile } from "node:fs/promises";
import ts from "typescript";

const configUrl = new URL("./vite.config.ts", import.meta.url);
const paletteUrl = new URL("./resources/css/theme/palette.css", import.meta.url);
const outputUrl = new URL("./resources/css/theme/nuxt-ui-colors.ide.css", import.meta.url);
const config = ts.createSourceFile("vite.config.ts", await readFile(configUrl, "utf8"), ts.ScriptTarget.Latest, true);
const palette = await readFile(paletteUrl, "utf8");
const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const property = (object, name) => object.properties.find((entry) => ts.isPropertyAssignment(entry) && entry.name.getText(config) === name)?.initializer;
let colors;

function visit(node) {
  if (ts.isCallExpression(node) && node.expression.getText(config) === "ui") {
    const options = node.arguments[0];
    const uiConfig = options && ts.isObjectLiteralExpression(options) ? property(options, "ui") : undefined;
    colors = uiConfig && ts.isObjectLiteralExpression(uiConfig) ? property(uiConfig, "colors") : undefined;
  }

  ts.forEachChild(node, visit);
}

visit(config);

if (!colors || !ts.isObjectLiteralExpression(colors)) {
  throw new Error("Cannot find the Nuxt UI color mapping in vite.config.ts.");
}

const declarations = colors.properties.flatMap((entry) => {
  if (!ts.isPropertyAssignment(entry) || !ts.isStringLiteral(entry.initializer)) {
    throw new Error("Nuxt UI color mappings must be literal properties and palette names.");
  }

  const role = entry.name.getText(config).replace(/^['"]|['"]$/g, "");
  const paletteName = entry.initializer.text;

  return shades.map((shade) => {
    const token = `--color-${paletteName}-${shade}`;

    if (!palette.includes(`${token}:`)) {
      throw new Error(`Missing palette token: ${token}`);
    }

    return `  --ui-color-${role}-${shade}: var(${token});`;
  });
});

await writeFile(outputUrl, `/* Generated from vite.config.ts by yarn ide:css. Do not edit.\n * IDE declarations only: do not import into application CSS.\n * Nuxt UI supplies these aliases at runtime.\n */\n:root {\n${declarations.join("\n")}\n}\n`);
console.log(`Generated ${declarations.length} Nuxt UI color declarations.`);
