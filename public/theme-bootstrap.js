(() => {
  const root = document.documentElement;
  const allowedAppearances = new Set(["system", "auto", "light", "dark"]);
  let appearance = root.dataset.appearance || "system";

  try {
    const storedAppearance = window.localStorage.getItem("vueuse-color-scheme");

    if (storedAppearance && allowedAppearances.has(storedAppearance)) {
      appearance = storedAppearance;
    }
  } catch {
    appearance = root.dataset.appearance || "system";
  }

  const normalizedAppearance = appearance === "auto" ? "system" : appearance;
  const isDark = normalizedAppearance === "dark" || (normalizedAppearance === "system" && window.matchMedia("(prefers-color-scheme: dark)").matches);
  const secure = window.location.protocol === "https:" ? "; Secure" : "";

  document.cookie = `appearance=${normalizedAppearance}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
  root.dataset.appearance = normalizedAppearance;
  root.classList.toggle("dark", isDark);
  root.style.colorScheme = isDark ? "dark" : "light";
})();
