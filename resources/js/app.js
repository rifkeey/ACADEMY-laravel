/*
 * AMANIQ Academy front end. Alpine's CSP build is used, so no code is ever evaluated from HTML
 * attribute strings: every interactive behaviour is a named component registered below.
 */
import Alpine from '@alpinejs/csp';
import { confirmDialog, countdown, knowledgeCheck } from './components/basics';
import { evidenceExplorer, kqlConsole, labRunner, workspaceTabs } from './components/lab-runner';
import { examTaker } from './components/exam-taker';

Alpine.data('confirmDialog', confirmDialog);
Alpine.data('knowledgeCheck', knowledgeCheck);
Alpine.data('countdown', countdown);
Alpine.data('labRunner', labRunner);
Alpine.data('evidenceExplorer', evidenceExplorer);
Alpine.data('kqlConsole', kqlConsole);
Alpine.data('workspaceTabs', workspaceTabs);
Alpine.data('examTaker', examTaker);

window.Alpine = Alpine;
Alpine.start();
