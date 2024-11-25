import { Link } from "react-router-dom";

export function NavLink({ children, className, href, isReactRoute }) {
    return isReactRoute ? (
        <Link
            to={href}
            className="inline-block rounded-lg px-2 py-1 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900"
        >
            {children}
        </Link>
    ) : (
        <a href={href} className={className}>
            {children}
        </a>
    );
}
