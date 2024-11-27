import { Dialog, DialogPanel } from "@headlessui/react";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useSession } from "../Contexts/SessionContext";
import { UnfinishedSessionModal } from "../Components/UnfinishedSessionModal";
import { Container } from "../Components/Container";
import { Button } from "../Components/Button";

const logos = {
    laravel: new URL("../../assets/logos/laravel.svg", import.meta.url).href,
    mirage: new URL("../../assets/logos/mirage.svg", import.meta.url).href,
    statamic: new URL("../../assets/logos/statamic.svg", import.meta.url).href,
    staticKit: new URL("../../assets/logos/statickit.svg", import.meta.url)
        .href,
    transistor: new URL("../../assets/logos/transistor.svg", import.meta.url)
        .href,
    tuple: new URL("../../assets/logos/tuple.svg", import.meta.url).href,
};

export default function Home() {
    const [showModal, setShowModal] = useState(false);
    const [sessionId, setSessionId] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    const { checkExistingSession } = useSession();

    const companies = [
        [
            { name: "Transistor", logo: logos.transistor },
            { name: "Tuple", logo: logos.tuple },
            { name: "StaticKit", logo: logos.staticKit },
        ],
        [
            { name: "Mirage", logo: logos.mirage },
            { name: "Laravel", logo: logos.laravel },
            { name: "Statamic", logo: logos.statamic },
        ],
    ];

    const handleSessionCheck = async () => {
        setIsLoading(true);
        setError("");

        try {
            // Vérifier si l'expérience existe
            const response = await fetch(
                `/api/experiment/session/${sessionId}`
            );
            const expData = await response.json();

            if (!expData.experiment) {
                setError("Code d'expérience invalide");
                return;
            }

            // Utiliser le context pour vérifier la session existante
            const hasUnfinishedSession = checkExistingSession(sessionId);
            if (!hasUnfinishedSession) {
                navigate(`/login/${sessionId}`);
            }
        } catch (error) {
            setError("Une erreur s'est produite");
        } finally {
            setIsLoading(false);
        }
    };

    return (
        // <div className="bg-white">
        //     <div className="relative isolate pt-14">
        //         <div className="py-24 sm:py-32 lg:pb-40">
        //             <div className="mx-auto max-w-7xl px-6 lg:px-8">
        //                 <div className="mx-auto max-w-2xl text-center">
        //                     <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
        //                         Plateforme d'Expérimentation Interactive
        //                     </h1>
        //                     <p className="mt-6 text-lg leading-8 text-gray-600">
        //                         Participez à des expériences innovantes et
        //                         contribuez à l'avancement de la recherche.
        //                     </p>
        //                     <div className="mt-10 flex items-center justify-center gap-x-6">
        //                         <button
        //                             onClick={() => setShowModal(true)}
        //                             className="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        //                         >
        //                             Démarrer une expérience
        //                         </button>
        //                         <button
        //                             onClick={() => navigate("/experiments")}
        //                             className="text-sm font-semibold leading-6 text-gray-900"
        //                         >
        //                             Voir les expériences{" "}
        //                             <span aria-hidden="true">→</span>
        //                         </button>
        //                     </div>
        //                 </div>
        //             </div>
        //         </div>

        //     </div>
        // </div>

        <Container className="pb-16 pt-20 text-center lg:pt-32">
            <h1 className="mx-auto max-w-4xl font-display text-5xl font-medium tracking-tight text-slate-900 sm:text-7xl">
                Accounting{" "}
                <span className="relative whitespace-nowrap text-blue-600">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 418 42"
                        className="absolute left-0 top-2/3 h-[0.58em] w-full fill-blue-300/70"
                        preserveAspectRatio="none"
                    >
                        <path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z" />
                    </svg>
                    <span className="relative">made simple</span>
                </span>{" "}
                for small businesses.
            </h1>
            <p className="mx-auto mt-6 max-w-2xl text-lg tracking-tight text-slate-700">
                Most bookkeeping software is accurate, but hard to use. We make
                the opposite trade-off, and hope you don’t get audited.
            </p>
            <div className="mt-10 flex justify-center gap-x-6">
                <Button onClick={() => setShowModal(true)} href="/register">
                    Start experimentation
                </Button>
                <Button
                    href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                    variant="outline"
                >
                    <svg
                        aria-hidden="true"
                        className="h-3 w-3 flex-none fill-blue-600 group-active:fill-current"
                    >
                        <path d="m9.997 6.91-7.583 3.447A1 1 0 0 1 1 9.447V2.553a1 1 0 0 1 1.414-.91L9.997 5.09c.782.355.782 1.465 0 1.82Z" />
                    </svg>
                    <span className="ml-3">Watch video</span>
                </Button>
            </div>

            <div className="mt-36 lg:mt-44">
                <p className="font-display text-base text-slate-900">
                    Trusted by these six companies so far
                </p>
                <ul
                    role="list"
                    className="mt-8 flex items-center justify-center gap-x-8 sm:flex-col sm:gap-x-0 sm:gap-y-10 xl:flex-row xl:gap-x-12 xl:gap-y-0"
                >
                    {companies.map((group, groupIndex) => (
                        <li key={groupIndex}>
                            <ul
                                role="list"
                                className="flex flex-col items-center gap-y-8 sm:flex-row sm:gap-x-12 sm:gap-y-0"
                            >
                                {group.map((company) => (
                                    <li key={company.name} className="flex">
                                        <img
                                            src={company.logo}
                                            alt={company.name}
                                        />
                                    </li>
                                ))}
                            </ul>
                        </li>
                    ))}
                </ul>
            </div>

            {/* Modal code d'expérience */}
            {showModal && (
                <Dialog
                    open={showModal}
                    onClose={() => setShowModal(false)}
                    className="relative z-50"
                >
                    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    <div className="fixed inset-0 z-10 overflow-y-auto">
                        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <DialogPanel className="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                <div>
                                    <div className="mt-3 text-center sm:mt-5">
                                        <h3 className="text-2xl font-semibold leading-6 text-gray-900 mb-4">
                                            Code d'accès à l'expérience
                                        </h3>
                                        <form
                                            onSubmit={(e) => {
                                                e.preventDefault();
                                                handleSessionCheck();
                                            }}
                                        >
                                            <input
                                                type="text"
                                                placeholder="Entrez le code d'expérience"
                                                value={sessionId}
                                                onChange={(e) =>
                                                    setSessionId(e.target.value)
                                                }
                                                className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 mb-4 px-3"
                                                disabled={isLoading}
                                            />
                                            {error && (
                                                <p className="text-red-500 text-sm mb-4">
                                                    {error}
                                                </p>
                                            )}
                                            <div className="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                                <button
                                                    type="submit"
                                                    disabled={isLoading}
                                                    className="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2"
                                                >
                                                    {isLoading ? (
                                                        <div className="flex items-center justify-center">
                                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                            Vérification...
                                                        </div>
                                                    ) : (
                                                        "Valider"
                                                    )}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setShowModal(false)
                                                    }
                                                    className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                                    disabled={isLoading}
                                                >
                                                    Annuler
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </DialogPanel>
                        </div>
                    </div>
                </Dialog>
            )}

            {/* Utilisation du composant UnfinishedSessionModal */}
            <UnfinishedSessionModal />
        </Container>
    );
}
