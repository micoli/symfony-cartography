import hljs from 'highlight.js/src/core';
import php from 'highlight.js/src/languages/php';
import twig from 'highlight.js/src/languages/twig';

hljs.registerLanguage('php', php);
hljs.registerLanguage('twig', twig);

hljs.highlightAll();
