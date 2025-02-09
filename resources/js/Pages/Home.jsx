import { Container } from "../Components/Container";
import { ChevronsDown, Icon, Info } from "lucide-react";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";
import { useTranslation } from "../Contexts/LanguageContext";

const logos = {
    ut2j: new URL("../../assets/logos/univ.png", import.meta.url).href,
    clle: new URL("../../assets/logos/cnrs.png", import.meta.url),
    anr: new URL("../../assets/logos/MSHS.png", import.meta.url).href,
};

const backgroundImage = new URL(
    "../../assets/bg/background-features.jpg",
    import.meta.url
).href;

export default function Home() {
    const { t } = useTranslation();

    const companies = [
        [
            { name: "JJ", logo: logos.ut2j },
            { name: "clle", logo: logos.clle },
            { name: "anr", logo: logos.anr },
        ],
    ];

    const scrollToNextSection = () => {
        const nextSection = document.querySelector("section");
        if (nextSection) {
            nextSection.scrollIntoView({ behavior: "smooth" });
        }
    };

    return (
        <>
            <div className="relative container mx-auto h-screen flex flex-col justify-center items-center space-y-6 md:space-y-16 mt-14">
                <Container className="pb-16 text-center">
                    <h1 className="mx-auto max-w-5xl font-display text-5xl font-medium tracking-tight text-slate-900 sm:text-7xl">
                        {t("home.title")}{" "}
                        <span className="relative whitespace-nowrap text-blue-600">
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 418 42"
                                className="absolute left-0 top-2/3 h-[0.58em] w-full fill-blue-300/70"
                                preserveAspectRatio="none"
                            >
                                <path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z" />
                            </svg>
                            <span className="relative">
                                {t("home.titleHighlight")}
                            </span>
                        </span>{" "}
                    </h1>
                    <p className="mx-auto mt-16 max-w-4xl text-lg tracking-tight text-slate-700 text-justify">
                        {t("home.description")}
                        {t("home.description2")}
                    </p>

                    <div className="mt-10">
                        <ul
                            role="list"
                            className="flex items-center justify-center gap-x-8 sm:flex-col sm:gap-x-0 sm:gap-y-10 xl:flex-row xl:gap-x-12 xl:gap-y-0"
                        >
                            {companies.map((group, groupIndex) => (
                                <li key={groupIndex}>
                                    <ul
                                        role="list"
                                        className="flex flex-col items-center gap-y-8 sm:flex-row sm:gap-x-12 sm:gap-y-0"
                                    >
                                        {group.map((company) => (
                                            <li
                                                key={company.name}
                                                className="flex"
                                            >
                                                <img
                                                    src={company.logo}
                                                    alt={company.name}
                                                    className="h-16 w-auto"
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="absolute bottom-24 left-1/2 transform -translate-x-1/2">
                        <button
                            onClick={scrollToNextSection}
                            className="group flex flex-col items-center gap-2 transition-opacity hover:opacity-80 animate-bounce"
                            aria-label="Défiler vers le bas"
                        >
                            <div className="flex justify-center items-center border-2 rounded-full border-slate-400 w-14 h-14 bg-slate-100 backdrop-blur-sm shadow-lg group-hover:border-slate-600">
                                <ChevronsDown className="text-slate-500 group-hover:text-slate-700" />
                            </div>
                        </button>
                    </div>
                </Container>
            </div>

            <section className="relative overflow-hidden bg-blue-600 py-20">
                <img
                    className="absolute left-1/2 top-1/2 max-w-none translate-x-[-44%] translate-y-[-42%] opacity-25"
                    src={backgroundImage}
                    alt=""
                    width={2245}
                    height={1636}
                />
                <Container className="relative">
                    <div className="max-w-3xl md:mx-auto md:text-center xl:max-w-none">
                        <h2 className="font-display text-3xl tracking-tight text-white sm:text-4xl md:text-4xl mb-8">
                            {t("home.objectives.title")}
                        </h2>
                        <div className="space-y-6 flex flex-col justify-center">
                            <p className="text-lg tracking-tight text-blue-50">
                                1. {t("home.objectives.description1")}
                            </p>
                            <p className="text-lg tracking-tight text-blue-50">
                                2. {t("home.objectives.description2")}
                            </p>
                        </div>
                    </div>
                </Container>
            </section>

            <section className="bg-white py-16">
                <Container>
                    <div className="max-w-3xl mx-auto text-center">
                        <div className="space-y-4">
                            <p className="text-slate-600 text-lg">
                                {t("home.privacy.gdpr")}
                            </p>
                            <a
                                href={t("home.privacy.link")}
                                className="inline-block text-blue-600 hover:text-blue-500 font-semibold"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {t("home.common.learn_more")} →
                            </a>
                        </div>
                    </div>
                </Container>
            </section>

            <FloatingLanguageButton />
        </>
    );
}
