import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { Container } from "../Components/Container";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

function Privacy() {
    const { t } = useTranslation();

    return (
        <div className="mt-32">
            <Container>
                <div className="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm p-8 py-16">
                    <div className="space-y-8">
                        <section>
                            <h1 className="text-2xl font-bold text-slate-900 mb-4">
                                {t("privacy.title")}
                            </h1>
                            <p
                                className="text-slate-600 leading-relaxed"
                                dangerouslySetInnerHTML={{
                                    __html: t("privacy.intro"),
                                }}
                            ></p>
                            <p className="text-slate-600 leading-relaxed mt-4">
                                {t("privacy.dataCollected")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.context.title")}
                            </h2>
                            <ul className="list-disc pl-6 space-y-2 text-slate-600">
                                <li>{t("privacy.context.directory")}</li>
                                <li>{t("privacy.context.newsletter")}</li>
                                <li>{t("privacy.context.events")}</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.recipients.title")}
                            </h2>
                            <p className="text-slate-600 mb-2">
                                {t("privacy.recipients.intro")}
                            </p>
                            <ul className="list-disc pl-6 space-y-2 text-slate-600">
                                <li>{t("privacy.recipients.visitors")}</li>
                                <li>{t("privacy.recipients.admins")}</li>
                                <li>{t("privacy.recipients.organizers")}</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.legalBasis.title")}
                            </h2>
                            <p className="text-slate-600">
                                {t("privacy.legalBasis.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.retention.title")}
                            </h2>
                            <ul className="list-disc pl-6 space-y-2 text-slate-600">
                                <li>{t("privacy.retention.newsletter")}</li>
                                <li>{t("privacy.retention.content")}</li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.transfer.title")}
                            </h2>
                            <p className="text-slate-600">
                                {t("privacy.transfer.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-3">
                                {t("privacy.rights.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p>{t("privacy.rights.description")}</p>
                                <p>
                                    {t("privacy.rights.contact.mshs")}
                                    <a
                                        href="mailto:mshst-communication@univ-tlse2.fr"
                                        className="text-blue-500 font-semibold"
                                    >
                                        mshst-communication@univ-tlse2.fr
                                    </a>
                                </p>
                                <p>
                                    {t("privacy.rights.contact.dpd")}
                                    <a
                                        href="mailto:dpd.demandes@cnrs.fr"
                                        className="text-blue-500 font-semibold"
                                    >
                                        dpd.demandes@cnrs.fr
                                    </a>
                                </p>
                                <p>
                                    {t("privacy.rights.cnil")}
                                    <a
                                        href="https://www.cnil.fr/fr/webform/adresser-une-plainte"
                                        className="text-blue-500 font-semibold"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        https://www.cnil.fr/fr/webform/adresser-une-plainte
                                    </a>
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </Container>
            <FloatingLanguageButton />
        </div>
    );
}

export default Privacy;
