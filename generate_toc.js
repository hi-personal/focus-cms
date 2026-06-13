import fs from 'fs';
import path from 'path';

// Configuration
const TOC_START = '=== TOC START ===';
const TOC_END = '=== TOC END ===';
const TAG_TYPES = ['ANCHOR', 'TODO', 'FIXME', 'STUB', 'NOTE', 'REVIEW', 'SECTION'];

function generateTOC(filePath) {
    try {
        // 1. Read file and detect type
        let content = fs.readFileSync(filePath, 'utf8');
        const ext = path.extname(filePath).toLowerCase();
        const isHtmlFile = ['.html', '.htm', '.xhtml', '.hxtml'].includes(ext) || filePath.endsWith('.blade.php');
        const isCssFile = ext === '.css';
        const isJsFile = ['.js', '.cjs', '.mjs'].includes(ext);
        const isMdFile = ext === '.md';

        // 2. Remove existing TOC
        content = removeExistingTOC(content, isHtmlFile, isCssFile, isMdFile).trim();

        // 3. Read all lines without TOC
        let lines = content.split('\n');
        while (lines.length > 0 && lines[0].trim() === '') {
            lines.shift();
        }

        // 4. Find all tags with original line numbers
        const tags = findTags(lines, isHtmlFile, isCssFile, isJsFile, isMdFile);

        if (tags.length === 0) {
            console.log('⚠️ No comment tags found in the file.');
            return;
        }

        // 5. Generate TOC content (as single block)
        const tocContent = generateTOCContent(tags, isHtmlFile, isCssFile, filePath, isJsFile, isMdFile);
        const tocLines = tocContent.split('\n').length;

        // 6. Adjust line numbers (account for 2 empty lines after TOC)
        const adjustedTags = tags.map(tag => ({
            ...tag,
            line: tag.line + tocLines + 2
        }));

        // 7. Generate final TOC with correct line numbers
        const finalToc = generateTOCContent(adjustedTags, isHtmlFile, isCssFile, filePath, isJsFile, isMdFile);

        // 8. Build final content with exactly 2 empty lines after TOC
        content = finalToc + '\n\n\n' + lines.join('\n');

        // 9. Save file
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`✅ TOC generated successfully for: ${filePath}`);
    } catch (error) {
        console.error(`❌ Error processing ${filePath}: ${error.message}`);
    }
}

function removeExistingTOC(content, isHtmlFile, isCssFile, isMdFile) {
    // Match the entire TOC block including opening/closing tags
    const htmlPattern = /<!-- === TOC START ===[\s\S]*?=== TOC END === -->/g;
    const cssPattern = /\/\* === TOC START ===[\s\S]*?=== TOC END === \*\//g;
    const jsPattern = /\/\/ === TOC START ===[\s\S]*?=== TOC END ===/g;
    const mdPattern = isMdFile ? /(<!--|\/\/) === TOC START ===[\s\S]*?=== TOC END === (-->)?/g : null;

    if (isHtmlFile) {
        return content.replace(htmlPattern, '');
    } else if (isCssFile) {
        return content.replace(cssPattern, '');
    } else if (isMdFile) {
        return content.replace(mdPattern, '');
    }
    return content.replace(jsPattern, '');
}

function findTags(lines, isHtmlFile, isCssFile, isJsFile, isMdFile) {
    const tags = [];
    let inCommentBlock = false;
    let commentStartLine = 0;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const lineNumber = i + 1;

        // HTML/Markdown comments
        if (isHtmlFile || isMdFile) {
            // Match HTML-style comments <!-- -->
            const htmlMatch = line.match(/<!--\s*([A-Z]+)(?:\[([^\]]+)\])?\s*(.*?)\s*-->/i);
            if (htmlMatch && TAG_TYPES.includes(htmlMatch[1].toUpperCase())) {
                tags.push({
                    originalContent: line.trim(),
                    line: lineNumber,
                    tag: htmlMatch[1].toUpperCase(),
                    id: htmlMatch[2] ? `[${htmlMatch[2]}] ` : '',
                    title: htmlMatch[3]?.trim() || ''
                });
                continue;
            }

            // Match double-slash comments // for Markdown
            if (isMdFile) {
                const mdMatch = line.match(/\/\/\s*([A-Z]+)(?:\[([^\]]+)\])?\s*(.*)/i);
                if (mdMatch && TAG_TYPES.includes(mdMatch[1].toUpperCase())) {
                    tags.push({
                        originalContent: line.trim(),
                        line: lineNumber,
                        tag: mdMatch[1].toUpperCase(),
                        id: mdMatch[2] ? `[${mdMatch[2]}] ` : '',
                        title: mdMatch[3]?.trim() || ''
                    });
                    continue;
                }
            }
        }

        // Detect comment block start
        if (!inCommentBlock && line.includes('/*') && !line.includes('*/')) {
            inCommentBlock = true;
            commentStartLine = lineNumber;
        }

        // Process comment blocks
        if (inCommentBlock) {
            const tagPattern = new RegExp(
                `(?:^|\\*\\s*)(${TAG_TYPES.join('|')})(?:\\[([^\\]]+)\\])?\\s*(.*)`,
                'i'
            );

            const match = line.match(tagPattern);
            if (match && TAG_TYPES.includes(match[1].toUpperCase())) {
                tags.push({
                    originalContent: line.trim(),
                    line: commentStartLine,
                    tag: match[1].toUpperCase(),
                    id: match[2] ? `[${match[2]}] ` : '',
                    title: match[3]?.trim() || ''
                });
            }

            if (line.includes('*/')) {
                inCommentBlock = false;
            }
            continue;
        }

        // Single-line comments
        if (isJsFile || isCssFile) {
            const singleLineMatch = line.match(/\/[\/\*]\s*([A-Z]+)(?:\[([^\]]+)\])?\s*(.*)/i);
            if (singleLineMatch && TAG_TYPES.includes(singleLineMatch[1].toUpperCase())) {
                tags.push({
                    originalContent: line.trim(),
                    line: lineNumber,
                    tag: singleLineMatch[1].toUpperCase(),
                    id: singleLineMatch[2] ? `[${singleLineMatch[2]}] ` : '',
                    title: singleLineMatch[3]?.trim() || ''
                });
            }
        }
    }

    return tags;
}

function generateTOCContent(tags, isHtmlFile, isCssFile, filePath, includeFilePath, isMdFile) {
    const tocLines = [];

    // Opening tag and TOC start on same line
    if (isHtmlFile) {
        tocLines.push('<!-- === TOC START ===');
    } else if (isCssFile) {
        tocLines.push('/* === TOC START ===');
    } else if (isMdFile) {
        // Use double-slash style for Markdown files
        tocLines.push('// === TOC START ===');
    } else {
        tocLines.push('// === TOC START ===');
    }

    // Header
    tocLines.push('// 🔹 Table of Contents (Generated)');
    tocLines.push('//');

    // File path
    if (includeFilePath) {
        tocLines.push(`// file: ${path.relative(process.cwd(), filePath)}`);
        tocLines.push('//');
    }

    // Group tags by type
    const groupedTags = {};
    TAG_TYPES.forEach(type => {
        groupedTags[type] = tags.filter(tag => tag.tag === type);
    });

    // Add tags to TOC
    TAG_TYPES.forEach(type => {
        if (groupedTags[type].length > 0) {
            tocLines.push(`// === ${type} ===`);
            groupedTags[type].forEach(tag => {
                tocLines.push(`// ${tag.line}#${tag.tag}: ${tag.id}${tag.title}`.trim());
            });
            tocLines.push('//');
        }
    });

    // Footer and closing tag on same line
    if (isHtmlFile) {
        tocLines.push('=== TOC END === -->');
    } else if (isCssFile) {
        tocLines.push('=== TOC END === */');
    } else if (isMdFile) {
        // Use double-slash style for Markdown files
        tocLines.push('// === TOC END ===');
    } else {
        tocLines.push('// === TOC END ===');
    }

    return tocLines.join('\n');
}

// Execute
const filePath = process.argv[2];
if (!filePath) {
    console.error('⚠️ Error: Please provide a file path!');
    console.error('Example: node generate_toc.js myfile.js');
    process.exit(1);
}

generateTOC(filePath);