/*
 * AMANIQ Academy front end. Alpine's CSP build is used, so no code is ever evaluated from HTML
 * attribute strings: every interactive behaviour is a named component registered below.
 */
import Alpine from '@alpinejs/csp';
import { confirmDialog, countdown, knowledgeCheck, printPage } from './components/basics';
import { evidenceExplorer, kqlConsole, labRunner, workspaceTabs } from './components/lab-runner';
import { examTaker } from './components/exam-taker';

Alpine.data('confirmDialog', confirmDialog);
Alpine.data('knowledgeCheck', knowledgeCheck);
Alpine.data('countdown', countdown);
Alpine.data('printPage', printPage);
Alpine.data('labRunner', labRunner);
Alpine.data('evidenceExplorer', evidenceExplorer);
Alpine.data('kqlConsole', kqlConsole);
Alpine.data('workspaceTabs', workspaceTabs);
Alpine.data('examTaker', examTaker);

// Header dropdowns are native <details> menus (they work without JavaScript); this only closes
// an open one when the visitor clicks elsewhere or presses Escape.
const closeDropdowns = (except) => document.querySelectorAll('details[data-dropdown][open]').forEach((d) => { if (d !== except) d.open = false; });
document.addEventListener('click', (e) => closeDropdowns(e.target.closest('details[data-dropdown]')));
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDropdowns(null); });

window.Alpine = Alpine;
Alpine.start();
