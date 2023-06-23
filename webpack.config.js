const path = require("path");

module.exports = {
  resolve: {
    alias: {
      "~": path.resolve("resources/js"),
      "@admin": path.resolve("resources/js/admin"),
    },
  },
};