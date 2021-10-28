module.exports = {
  future: {
    purgeLayersByDefault: true,
    removeDeprecatedGapUtilities: true,
  },
  purge: {
    content: [
     "./src/**/*.svelte",

    ],
    enabled: true
  },
};