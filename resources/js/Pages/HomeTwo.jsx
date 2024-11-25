import { Dialog, DialogPanel } from "@headlessui/react";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useSession } from "../Contexts/SessionContext";
import { UnfinishedSessionModal } from "../Components/UnfinishedSessionModal";
import { Container } from "../Components/Container";
import { Button } from "../Components/Button";
import { ChevronsDown } from "lucide-react";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

const logos = {
    ut2j: new URL("../../assets/logos/ut2j_logo.png", import.meta.url).href,
    clle: new URL("../../assets/logos/logo_clle.png", import.meta.url),
    anr: new URL("../../assets/logos/anr_logo.png", import.meta.url).href,
};

const backgroundImage = new URL(
    "../../assets/bg/background-features.jpg",
    import.meta.url
).href;

export default function HomeTwo() {
    const [showModal, setShowModal] = useState(false);
    const [sessionId, setSessionId] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    const { checkExistingSession } = useSession();

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
        <>
            <div className="-mt-28 relative container mx-auto h-screen flex flex-col justify-center items-center space-y-6 md:space-y-16">
                <Container className="pb-16 text-center">
                    <h1 className="mx-auto max-w-5xl font-display text-5xl font-medium tracking-tight text-slate-900 sm:text-7xl">
                        Plateforme d'expérimentation{" "}
                        <span className="relative whitespace-nowrap text-blue-600">
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 418 42"
                                className="absolute left-0 top-2/3 h-[0.58em] w-full fill-blue-300/70"
                                preserveAspectRatio="none"
                            >
                                <path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z" />
                            </svg>
                            <span className="relative">Auditive</span>
                        </span>{" "}
                    </h1>
                    <p className="mx-auto mt-16 max-w-4xl text-lg tracking-tight text-slate-700 text-justify">
                        TCL-LabX est un outil dédié à l'étude de la perception
                        auditive, spécialement conçu pour la recherche sur les
                        implants cochléaires. Il permet d'analyser comment les
                        personnes portant des implants cochléaires catégorisent
                        et perçoivent les différents sons naturels de leur
                        environnement.
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
                                                    className="h-28 w-auto"
                                                    unoptimized
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
                    className="absolute left-1/2 top-1/2 max-w-none translate-x-[-44%] translate-y-[-42%]"
                    src={backgroundImage}
                    alt=""
                    width={2245}
                    height={1636}
                    unoptimized
                />
                <Container className="relative">
                    <div className="max-w-3xl md:mx-auto md:text-center xl:max-w-none">
                        <h2 className="font-display text-3xl tracking-tight text-white sm:text-4xl md:text-5xl">
                            Quel est l'objectif de l'étude ?
                        </h2>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            TCL-LabX est un outil dédié à l'étude de la
                            perception auditive, spécialement conçu pour la
                            recherche sur les implants cochléaires. Il permet
                            d'analyser comment les personnes portant des
                            implants cochléaires catégorisent et perçoivent les
                            différents sons naturels de leur environnement.
                        </p>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            Cette étude vise à mieux comprendre les stratégies
                            de catégorisation des sons par les patients
                            implantés, contribuant ainsi à l'amélioration des
                            techniques de réhabilitation auditive.
                        </p>
                    </div>
                </Container>
            </section>

            <section className="relative overflow-hidden bg-white py-20">
                <Container className="relative">
                    <div className="max-w-3xl md:mx-auto md:text-center xl:max-w-none">
                        <h2 className="font-display text-3xl tracking-tight text-black sm:text-4xl md:text-5xl">
                            Comment participer ?
                        </h2>
                        <p className="mt-6 text-lg tracking-tight text-black text-justify">
                            Votre participation est entièrement volontaire et
                            anonyme. L'expérience se déroule en plusieurs étapes
                            :
                        </p>
                        <ul className="mt-4 list-disc text-lg pl-20 text-justify">
                            <li className="mt-2">
                                Écoute de différents sons naturels
                            </li>
                            <li className="mt-2">
                                Catégorisation libre selon vos propres critères
                            </li>
                            <li className="mt-2">
                                Bref questionnaire sur votre expérience
                            </li>
                        </ul>
                        <p className="mt-6 text-lg tracking-tight text-black text-justify">
                            Vous pouvez interrompre l'expérience à tout moment.
                            Vos données resteront strictement confidentielles et
                            seront utilisées uniquement à des fins de recherche.
                        </p>
                    </div>
                </Container>
            </section>

            <section className="relative overflow-hidden bg-blue-600 py-20">
                <img
                    className="absolute left-1/2 top-1/2 max-w-none translate-x-[-44%] translate-y-[-42%]"
                    src={backgroundImage}
                    alt=""
                    width={2245}
                    height={1636}
                    unoptimized
                />
                <Container className="relative">
                    <div className="max-w-3xl md:mx-auto md:text-center xl:max-w-none">
                        <h2 className="font-display text-3xl tracking-tight text-white sm:text-4xl md:text-5xl">
                            Vos droits à la confidentialité et au respect de la
                            vie privée
                        </h2>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            Votre participation à cette recherche est
                            volontaire. Vous êtes donc libre de refuser de
                            participer sans avoir à vous justifier. Si vous
                            participez, vous pouvez décider à tout moment
                            d’interrompre votre participation sans avoir à vous
                            justifier et sans encourir aucune responsabilité ni
                            aucun préjudice de ce fait.
                        </p>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            La durée de conservation de vos données est de 10
                            ans après la publication des résultats de recherche.
                            Au-delà de cette période, elles seront effacées.
                        </p>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            Seuls le responsable scientifique et les chercheurs
                            associés à ce projet auront accès à vos données. Les
                            données recueillies seront traitées de façon
                            conforme au Règlement général sur la protection des
                            données (RGPD).
                        </p>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            Si vous estimez, après nous avoir contactés, que vos
                            droits sur vos données ne sont pas respectés, vous
                            pouvez adresser une réclamation (plainte) à la CNIL
                            :
                            https://www.cnil.fr/fr/webform/adresser-une-plainte.
                        </p>
                        <p className="mt-6 text-lg tracking-tight text-blue-100 text-justify">
                            Pour plus d'informations, n'hésitez pas à nous
                            contacter :
                        </p>
                        <ul className="mt-4 list-disc text-lg pl-20 text-justify text-white">
                            <li className="mt-2">
                                Pascal GAILLARD (MCF, Laboratoire CLLE,
                                Université Toulouse 2 Jean Jaurès -
                                pascal.gaillard@univ-tlse2.fr), Responsable
                                Scientifique du projet
                            </li>
                        </ul>
                    </div>
                </Container>
            </section>

            <section className="relative overflow-hidden bg-white pb-28 pt-20 sm:py-32">
                <Container className="relative">
                    <div className="max-w-3xl md:mx-auto md:text-center xl:max-w-none flex flex-col justify-center items-center">
                        <p className="text-lg tracking-tight text-black text-justify max-w-2xl">
                            En cliquant ici, vous indiquez que vous avez bien lu
                            et compris les renseignements donnés et vous
                            consentez à participer à cette recherche
                        </p>
                        <Button
                            className="mt-12 py-4"
                            onClick={() => setShowModal(true)}
                        >
                            <span className="font-semibold text-xl">
                                Commencer l'expérience
                            </span>
                        </Button>
                    </div>
                </Container>
            </section>

            <FloatingLanguageButton />

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
                                        <h3 className="text-3xl font-semibold leading-6 text-gray-900 mb-4">
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
                                                className="block w-full rounded-md border-0 py-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 mb-4 px-3"
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
        </>
    );
}
