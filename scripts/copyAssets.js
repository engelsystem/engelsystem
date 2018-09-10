const fs = require("fs");
const path = require("path");
const mkdirp = require("mkdirp");

const baseAssetPath = {
  src: path.resolve("resources/assets"),
  target: path.resolve("public/assets")
};

const emojiPath = {
  src: baseAssetPath.src + "/emojis",
  target: baseAssetPath.target + "/emojis"
};

mkdirp.sync(emojiPath.target);

const emojis = fs.readdirSync(emojiPath.src);

emojis.forEach(e => {
  fs.copyFile(`${emojiPath.src}/${e}`, `${emojiPath.target}/${e}`, e => {
    if (e) {
      console.error(e);
    }
  });
});
