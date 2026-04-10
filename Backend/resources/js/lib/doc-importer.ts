import * as mammoth from 'mammoth';
import { marked } from 'marked';

interface ImportedDoc {
    title: string;
    intro: string;
    sections: { sub_title: string; content: string; level: number }[];
}

export const importDocument = async (file: File): Promise<ImportedDoc> => {
    const extension = file.name.split('.').pop()?.toLowerCase();

    if (extension === 'md') {
        return parseMarkdown(await file.text(), file.name);
    } else if (extension === 'docx') {
        const arrayBuffer = await file.arrayBuffer();
        const result = await mammoth.convertToHtml({ arrayBuffer });
        return parseHtml(result.value);
    } else {
        throw new Error('Unsupported file format. Please upload .md or .docx');
    }
};

const parseMarkdown = async (text: string, filename: string): Promise<ImportedDoc> => {
    const lines = text.split('\n');
    let title = '';
    let introMarkdown = '';
    const sections: { sub_title: string; content: string; level: number }[] = [];

    let currentSectionTitle = '';
    let currentSectionContent = '';
    let currentSectionLevel = 2;
    let foundTitle = false;

    for (const line of lines) {
        if (!foundTitle && line.startsWith('# ')) {
            title = line.replace('# ', '').trim();
            foundTitle = true;
            continue;
        }

        const h2Match = line.startsWith('## ');
        const h3Match = line.startsWith('### ');

        if (h2Match || h3Match) {
            // Save previous section if it exists
            if (currentSectionTitle) {
                sections.push({
                    sub_title: currentSectionTitle,
                    content: await marked.parse(currentSectionContent.trim()) as string,
                    level: currentSectionLevel
                });
            }
            
            currentSectionLevel = h2Match ? 2 : 3;
            currentSectionTitle = line.replace(h2Match ? '## ' : '### ', '').trim();
            currentSectionContent = '';
            continue;
        }

        if (currentSectionTitle) {
            currentSectionContent += line + '\n';
        } else {
            introMarkdown += line + '\n';
        }
    }

    // Push last section
    if (currentSectionTitle) {
        sections.push({
            sub_title: currentSectionTitle,
            content: await marked.parse(currentSectionContent.trim()) as string,
            level: currentSectionLevel
        });
    }

    return {
        title: title || filename.replace(/\.[^/.]+$/, "").replace(/[-_]/g, ' '),
        intro: (await marked.parse(introMarkdown.trim())) as string,
        sections
    };
};

const parseHtml = (html: string): ImportedDoc => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    let title = doc.querySelector('h1')?.textContent || '';
    
    // Remove the h1 from the doc so it's not in the intro
    doc.querySelector('h1')?.remove();

    const sections: { sub_title: string; content: string; level: number }[] = [];
    const headings = Array.from(doc.querySelectorAll('h2, h3'));
    
    let introHtml = '';
    
    if (headings.length > 0) {
        // Everything before first heading is intro
        let currentNode = doc.body.firstChild;
        while (currentNode && currentNode !== headings[0]) {
            if (currentNode instanceof HTMLElement) {
                introHtml += currentNode.outerHTML;
            } else {
                introHtml += currentNode.textContent;
            }
            currentNode = currentNode.nextSibling;
        }

        // Process sections
        headings.forEach((heading, index) => {
            let content = '';
            let next = heading.nextSibling;
            const nextHeading = headings[index + 1];
            
            while (next && next !== nextHeading) {
                if (next instanceof HTMLElement) {
                    content += next.outerHTML;
                } else {
                    content += next.textContent;
                }
                next = next.nextSibling;
            }
            
            sections.push({
                sub_title: heading.textContent || 'Untitled Section',
                content: content.trim(),
                level: heading.tagName.toLowerCase() === 'h2' ? 2 : 3
            });
        });
    } else {
        introHtml = doc.body.innerHTML;
    }

    return {
        title: title.trim(),
        intro: introHtml.trim(),
        sections
    };
};
