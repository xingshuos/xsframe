/** 后台公共能力归属和旧 common 应用目录清理回归检查。 */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../../../..');
const appRoot = path.join(root, 'app');
const adminRoot = path.join(appRoot, 'admin');
const publicAdminRoot = path.join(root, 'public', 'app', 'admin');

/**
 * 递归读取运行时代码和模板文件。
 *
 * @param {string} directory 待读取目录
 * @returns {string[]} 文件路径列表
 */
function collectFiles(directory) {
    const result = [];
    for (const entry of fs.readdirSync(directory, {withFileTypes: true})) {
        const fullPath = path.join(directory, entry.name);
        if (entry.isDirectory()) {
            // 第三方归档文档不参与当前框架运行时架构检查。
            if (fullPath.includes(path.join('packages', 'docs', 'other'))) {
                continue;
            }
            result.push(...collectFiles(fullPath));
            continue;
        }
        if (entry.isFile() && /\.(php|html|js|css|cjs)$/.test(entry.name)) {
            result.push(fullPath);
        }
    }
    return result;
}

assert.equal(fs.existsSync(path.join(appRoot, 'common')), false, 'app/common 目录不应继续作为伪应用存在');

const requiredAdminFiles = [
    'service/UserPermService.php',
    'service/FilePreviewService.php',
    'service/FileUploadService.php',
    'service/ResourceDataDecorator.php',
    'service/UserIdentityTrait.php',
    'config/file_upload.php',
    'view/file_upload/index.html',
];
for (const relativePath of requiredAdminFiles) {
    assert.ok(fs.existsSync(path.join(adminRoot, relativePath)), `后台公共文件迁移缺失：${relativePath}`);
}

// admin 静态资源由 public/app/admin/static 直接提供，不走其他应用的 Nginx alias。
const requiredPublicAdminFiles = [
    'static/file-preview/file-preview.js',
    'static/file-upload/file-uploader.js',
    'static/todo/todo-core.js',
    'static/tree-table/tree-table.js',
];
for (const relativePath of requiredPublicAdminFiles) {
    assert.ok(fs.existsSync(path.join(publicAdminRoot, relativePath)), `后台公共静态资源迁移缺失：${relativePath}`);
}

const userPermSource = fs.readFileSync(path.join(adminRoot, 'service/UserPermService.php'), 'utf8');
assert.match(userPermSource, /namespace app\\admin\\service;/, 'UserPermService 未归属 app\\admin\\service');

for (const file of [...collectFiles(appRoot), ...collectFiles(publicAdminRoot)]) {
    const source = fs.readFileSync(file, 'utf8');
    assert.doesNotMatch(source, /app\\common\\service\\(?:UserPermService|FilePreviewService|FileUploadService|ResourceDataDecorator|UserIdentityTrait)/,
        `运行时代码仍引用已迁移的 common 服务：${path.relative(root, file)}`);
    assert.doesNotMatch(source, /\/app\/common\/static\//,
        `运行时代码仍引用旧 common 静态资源：${path.relative(root, file)}`);
}

console.log('通过：公共能力已归属 admin，app/common 伪应用目录及其运行时引用已清理。');
