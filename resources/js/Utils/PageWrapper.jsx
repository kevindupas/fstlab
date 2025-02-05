export const PageWrapper = ({ children, allowScroll = true }) => {
    return (
        <div
            className={`
            ${
                !allowScroll
                    ? "fixed inset-0 w-full h-full overflow-hidden"
                    : "min-h-screen"
            }
        `}
        >
            <div
                className={`
                h-full w-full
                ${allowScroll ? "scroll-container" : ""}
            `}
            >
                {children}
            </div>
        </div>
    );
};
