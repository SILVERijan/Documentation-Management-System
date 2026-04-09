import * as mammoth from 'mammoth';
import { marked } from 'marked';

interface ImportedDoc {
    title: string;
    intro: string;
    sections: { sub_title: string; content: string }[];
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
    const sections: { sub_title: string; content: string }[] = [];

    let currentSectionTitle = '';
    let currentSectionContent = '';
    let foundTitle = false;

    for (const line of lines) {
        if (!foundTitle && line.startsWith('# ')) {
            title = line.replace('# ', '').trim();
            foundTitle = true;
            continue;
        }

        if (line.startsWith('## ')) {
            // Save previous section if it exists
            if (currentSectionTitle) {
                sections.push({
                    sub_title: currentSectionTitle,
                    content: await marked.parse(currentSectionContent.trim())
                });
            } else if (!foundTitle && !title) {
                 // If no H1 found, use first H2 as title? 
                 // Better to just treat everything before first H2 as intro
            }
            
            currentSectionTitle = line.replace('## ', '').trim();
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
            content: await marked.parse(currentSectionContent.trim())
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

    const sections: { sub_title: string; content: string }[] = [];
    const h2s = Array.from(doc.querySelectorAll('h2'));
    
    let introHtml = '';
    
    if (h2s.length > 0) {
        // Everything before first H2 is intro
        let currentNode = doc.body.firstChild;
        while (currentNode && currentNode !== h2s[0]) {
            if (currentNode instanceof HTMLElement) {
                introHtml += currentNode.outerHTML;
            } else {
                introHtml += currentNode.textContent;
            }
            currentNode = currentNode.nextSibling;
        }

        // Process sections
        h2s.forEach((h2, index) => {
            let content = '';
            let next = h2.nextSibling;
            const nextH2 = h2s[index + 1];
            
            while (next && next !== nextH2) {
                if (next instanceof HTMLElement) {
                    content += next.outerHTML;
                } else {
                    content += next.textContent;
                }
                next = next.nextSibling;
            }
            
            sections.push({
                sub_title: h2.textContent || 'Untitled Section',
                content: content.trim()
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
