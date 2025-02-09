export function Logo({ desc, className }) {
    return (
        <img
            src="../logo/logo.png"
            alt={desc}
            className={className || "w-56 h-auto"}
        />
    );
}
