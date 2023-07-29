module.exports = {
  tagFormat: "${version}",
  branches: ["master"],
  plugins: [
      ["@semantic-release/npm", { npmPublish: false }],
      "@semantic-release/github",
      [
          "semantic-release-plugin-update-version-in-files",
          {
              "files": [
                  "wc-cart-pdf.php",
                  "readme.txt"
              ],
              "placeholder": "0.0.0-development"
          }
      ]
  ]
};