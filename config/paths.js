

const path = require('path');
const fs = require('fs');
const url = require('url');

// Make sure any symlinks in the project folder are resolved:
// https://github.com/facebook/create-react-app/issues/637
const appDirectory = fs.realpathSync(process.cwd());
const resolveApp = relativePath => path.resolve(appDirectory, relativePath);

const envPublicUrl = process.env.PUBLIC_URL;

function ensureSlash(inputPath, needsSlash) {
  const hasSlash = inputPath.endsWith('/');
  if (hasSlash && !needsSlash) {
    return inputPath.substr(0, inputPath.length - 1);
  } else if (!hasSlash && needsSlash) {
    return `${inputPath}/`;
  } else {
    return inputPath;
  }
}

const getPublicUrl = () => envPublicUrl ;

const moduleFileExtensions = [
  'web.mjs',
  'mjs',
  'web.js',
  'js',
  'web.ts',
  'ts',
  'web.tsx',
  'tsx',
  'json',
  'web.jsx',
  'jsx',
  'css'
];

// Resolve file paths in the same order as webpack
const resolveModule = (resolveFn, filePath) => {
  const extension = moduleFileExtensions.find(extension =>
    fs.existsSync(resolveFn(`${filePath}.${extension}`))
  );

  if (extension) {
    return resolveFn(`${filePath}.${extension}`);
  }

  return resolveFn(`${filePath}.js`);
};

// config after eject: we're in ./config/
module.exports = {
  appIndexJs: resolveModule(resolveApp, 'Resources/Private/App/src/index'),
  appPath: resolveApp('Resources/Private/JavaScript'),
  appBuild: path.resolve('Resources/Public/'),
  appSrc: resolveApp('Resources/Private/App/src'),
  appPublic: resolveApp('Resources/Private/App/Public'),
  appHtml: resolveApp('Resources/Private/App/index.html'),
  dotenv: resolveApp('.env'),

  appTsConfig: resolveApp('tsconfig.json'),

  appNodeModules: resolveApp('node_modules'),
  servedPath: path.resolve('./Resources/Public/'),
};



module.exports.moduleFileExtensions = moduleFileExtensions;
