import assert from "node:assert/strict";
import test from "node:test";

import { ESLint } from "eslint";

const eslint = new ESLint();

const restrictedImportMessages = async (code, filePath) => {
  const [result] = await eslint.lintText(code, { filePath });

  return result.messages.filter(({ ruleId }) => ruleId === "no-restricted-imports").map(({ message }) => message);
};

test("UI primitives cannot import generated transport code", async () => {
  const messages = await restrictedImportMessages('import { dashboard } from "@/routes";\nvoid dashboard;\n', "resources/js/components/Ui/Button/variants.ts");

  assert.equal(messages.length, 1);
  assert.match(messages[0], /UI primitives cannot depend on application, route, or generated transport layers\.$/);
});

test("UI primitive internals cannot be deep imported", async () => {
  const messages = await restrictedImportMessages('import type { ButtonProps } from "@/components/Ui/Button/types";\nvoid ({} as ButtonProps);\n', "resources/js/pages/ArchitectureFixture.ts");

  assert.equal(messages.length, 1);
  assert.match(messages[0], /Import UI primitives through their component-directory index\.ts public API\.$/);
});

test("application component internals cannot be deep imported", async () => {
  const messages = await restrictedImportMessages('import AppButton from "@/components/AppButton/AppButton.vue";\nvoid AppButton;\n', "resources/js/pages/ArchitectureFixture.ts");

  assert.equal(messages.length, 1);
  assert.match(messages[0], /Import application components through their component-directory index\.ts public API\.$/);
});

test("public component APIs remain importable", async () => {
  const messages = await restrictedImportMessages('import Button from "@/components/Ui/Button";\nimport AppButton from "@/components/AppButton";\nvoid Button;\nvoid AppButton;\n', "resources/js/pages/ArchitectureFixture.ts");

  assert.deepEqual(messages, []);
});
