export const nowSeconds = () => Math.floor(Date.now() / 1000);

const pad = (n) => String(n).padStart(2, "0");

export const formatTime = (s) => {
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return (h > 0 ? [h, m, sec] : [m, sec]).map(pad).join(":");
};
