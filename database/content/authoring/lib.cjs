// Shared helpers for the content apply scripts: load/save programme.json and the source register.
const fs = require('fs');
const path = require('path');
const ROOT = path.join(__dirname, '..');   // database/content

function load(dir) {
    const file = path.join(ROOT, 'programmes', dir, 'programme.json');
    return { file, data: JSON.parse(fs.readFileSync(file, 'utf8')) };
}
function save({ file, data }) {
    fs.writeFileSync(file, JSON.stringify(data, null, 2) + '\n');
}
/** Adds or updates sources in sources.json (by key). checkedOn records when the URL was verified. */
function upsertSources(list, checkedOn) {
    const file = path.join(ROOT, 'sources.json');
    const reg = JSON.parse(fs.readFileSync(file, 'utf8'));
    for (const s of list) {
        const i = reg.sources.findIndex((x) => x.key === s.key);
        const entry = { ...s, dateChecked: checkedOn };
        if (i >= 0) reg.sources[i] = { ...reg.sources[i], ...entry };
        else reg.sources.push(entry);
    }
    fs.writeFileSync(file, JSON.stringify(reg, null, 2) + '\n');
}
function replaceModule(p, slug, module) {
    const i = p.modules.findIndex((m) => m.slug === slug);
    if (i < 0) throw new Error(`module ${slug} not found`);
    p.modules[i] = module;
}
function replaceLab(p, slug, lab) {
    const i = p.labs.findIndex((l) => l.slug === slug);
    if (i >= 0) p.labs[i] = lab;
    else p.labs.push(lab);
}
/** Replaces all questions with the given key prefix. */
function replaceQuestions(p, prefix, questions) {
    p.questions = p.questions.filter((q) => !q.key.startsWith(prefix)).concat(questions);
    const keys = new Set();
    for (const q of p.questions) {
        if (keys.has(q.key)) throw new Error(`duplicate question key ${q.key}`);
        keys.add(q.key);
        const correct = q.options.filter((o) => o.correct).length;
        if (q.kind === 'single' ? correct !== 1 : correct < 1) throw new Error(`bad answer count in ${q.key}`);
    }
}
/** Single-choice helper: options listed with the correct one marked by a leading '*'. */
function single(key, category, difficulty, objective, prompt, options, explanation, tags = []) {
    return {
        key, kind: 'single', category, difficulty, objective, prompt, explanation, tags: [category, ...tags],
        options: options.map((o) => (o.startsWith('*') ? { text: o.slice(1), correct: true } : { text: o })),
    };
}
function multiple(key, category, difficulty, objective, prompt, options, explanation, tags = []) {
    return { ...single(key, category, difficulty, objective, prompt, options, explanation, tags), kind: 'multiple' };
}
module.exports = { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple };
