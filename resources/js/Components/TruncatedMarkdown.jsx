import React from "react";
import ReactMarkdown from "react-markdown";
import rehypeRaw from "rehype-raw";

function TruncatedMarkdown({ content = "", maxLength = 150, className }) {
    const truncateText = (text) => {
        if (!text) return "";
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + "...";
    };

    return (
        <div className={className}>
            <ReactMarkdown rehypePlugins={[rehypeRaw]} className="prose">
                {truncateText(content)}
            </ReactMarkdown>
        </div>
    );
}

export default TruncatedMarkdown;
