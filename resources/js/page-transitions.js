// Transitions are off while a page is swapped in, and on after it has painted.
// `livewire:navigated` also fires on the initial load.
const html = document.documentElement;

document.addEventListener("livewire:navigating", () =>
    html.classList.remove("transitions-ready"),
);

document.addEventListener("livewire:navigated", () =>
    requestAnimationFrame(() =>
        requestAnimationFrame(() => html.classList.add("transitions-ready")),
    ),
);
