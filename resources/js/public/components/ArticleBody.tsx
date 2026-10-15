import type { ReactNode } from 'react';

/**
 * Renders an article body written as plain text: blank line = new paragraph, "## " = heading, "- " = bullet list.
 * Nothing is injected as HTML, so admin-entered text can never run scripts.
 */
export default function ArticleBody({ text }: { text: string }) {
    const blocks = text.replace(/\r\n/g, '\n').split(/\n{2,}/).map((b) => b.trim()).filter(Boolean);

    return (
        <div className="space-y-4 text-lg leading-relaxed text-ink/85">
            {blocks.map((block, i): ReactNode => {
                if (block.startsWith('## ')) {
                    return <h2 key={i} className="pt-4 text-2xl font-extrabold text-ink">{block.slice(3)}</h2>;
                }
                const lines = block.split('\n');
                if (lines.every((l) => l.startsWith('- '))) {
                    return (
                        <ul key={i} className="list-disc space-y-1 pl-6">
                            {lines.map((l, j) => <li key={j}>{l.slice(2)}</li>)}
                        </ul>
                    );
                }
                return <p key={i} className="whitespace-pre-line">{block}</p>;
            })}
        </div>
    );
}
