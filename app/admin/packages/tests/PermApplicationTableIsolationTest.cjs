/** 系统员工管理不得依赖具体应用的教师表。 */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const adminRoot = path.resolve(__dirname, '../..');
const applicationTeacherTable = ['sz', 'jiaowu', 'teacher'].join('_');

function collectFiles(directory) {
    const files = [];
    for (const entry of fs.readdirSync(directory, {withFileTypes: true})) {
        const fullPath = path.join(directory, entry.name);
        if (entry.isDirectory()) {
            files.push(...collectFiles(fullPath));
        } else if (entry.isFile() && /\.(php|html|js|css)$/.test(entry.name)) {
            files.push(fullPath);
        }
    }
    return files;
}

for (const file of collectFiles(adminRoot)) {
    const source = fs.readFileSync(file, 'utf8');
    assert.doesNotMatch(
        source,
        new RegExp(applicationTeacherTable),
        `后台系统仍依赖应用教师表：${path.relative(adminRoot, file)}`
    );
}

const controller = fs.readFileSync(path.join(adminRoot, 'controller/Perm.php'), 'utf8');
const listView = fs.readFileSync(path.join(adminRoot, 'view/perm/user/list.html'), 'utf8');
const postView = fs.readFileSync(path.join(adminRoot, 'view/perm/user/post.html'), 'utf8');

assert.doesNotMatch(controller, /getUnboundTeachers|teacher_id|boundTeacher/);
assert.doesNotMatch(listView, /\['teacher'\]|关联教师/);
assert.doesNotMatch(postView, /teacher-|teacher_id|boundTeacher|关联教师/);

console.log('通过：系统员工管理已与应用教师表解耦。');
