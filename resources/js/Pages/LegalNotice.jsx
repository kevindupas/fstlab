import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { Container } from "../Components/Container";
import { div } from "framer-motion/client";

function LegalNotice() {
    const { t } = useTranslation();

    return (
        <div className="mt-32">
            <Container>
                <div className="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm p-8 py-16">
                    <h1 className="text-3xl font-bold text-slate-900 mb-8">
                        {t("legal.title")}
                    </h1>

                    <div className="space-y-8">
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article1.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article1.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article2.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article2.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article3.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article3.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article4.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed mb-4">
                                {t("legal.article4.intro")}
                            </p>
                            <ul className="list-disc pl-6 space-y-2 text-slate-600">
                                <li className="leading-relaxed">
                                    {t("legal.article4.item1")}
                                </li>
                                <li className="leading-relaxed">
                                    {t("legal.article4.item2")}
                                </li>
                                <li className="leading-relaxed">
                                    {t("legal.article4.item3")}
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article5.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article5.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article6.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article6.content")}
                            </p>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article7.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article7.content")}
                            </p>
                        </section>
                    </div>
                </div>
            </Container>
        </div>
    );
}

export default LegalNotice;
