export function Logo({ desc, className }) {
    return (
        <img
            src="../logo/logo.jpeg"
            alt={desc}
            className={className || "w-48 h-auto"}
        />
    );
}
