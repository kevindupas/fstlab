import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { Container } from "../Components/Container";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

function LegalNotice() {
    const { t } = useTranslation();

    return (
        <div className="mt-32">
            <Container>
                <div className="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm p-8 py-16">
                    <h1 className="text-3xl font-bold text-slate-900 mb-8">
                        {t("legal.title")}
                    </h1>

                    <div className="space-y-10">
                        {/* Article 1 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article1.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article1.para1")}
                                </p>

                                <div className="pl-4 border-l-2 border-slate-200 space-y-3">
                                    <p>
                                        <strong>
                                            {t("legal.article1.owner_label")}
                                        </strong>{" "}
                                        <a
                                            className="underline"
                                            href="https://www.cnrs.fr/"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {t("legal.article1.owner")}
                                        </a>
                                    </p>
                                    <p>
                                        <strong>
                                            {t("legal.article1.creator_label")}
                                        </strong>{" "}
                                        <a
                                            className="underline"
                                            href="https://mshs.univ-toulouse.fr/"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {t("legal.article1.creator")}
                                        </a>
                                    </p>
                                    <p>{t("legal.article1.publisher")}</p>
                                    <p>
                                        <strong>
                                            {t("legal.article1.host_label")}
                                        </strong>{" "}
                                        <a
                                            className="underline"
                                            href="https://www.univ-tlse2.fr/"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {t("legal.article1.host")}
                                        </a>
                                    </p>
                                    <p>
                                        <strong>
                                            {t("legal.article1.credits_label")}
                                        </strong>{" "}
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: t(
                                                    "legal.article1.credits_html"
                                                ),
                                            }}
                                        />
                                    </p>
                                    <p>
                                        <strong>
                                            {t("legal.article1.logo_label")}
                                        </strong>{" "}
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: t(
                                                    "legal.article1.logo_html"
                                                ),
                                            }}
                                        />
                                    </p>
                                </div>

                                <p className="leading-relaxed text-slate-500 italic text-sm">
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: t(
                                                "legal.article1.notice_html"
                                            ),
                                        }}
                                    />
                                </p>
                            </div>
                        </section>

                        {/* Articles 2-6 restent identiques, pas de liens à ajouter */}
                        {/* Article 2 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article2.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article2.para1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article2.para2")}
                                </p>
                            </div>
                        </section>

                        {/* Article 3 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article3.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article3.para1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article3.para2")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article3.para3")}
                                </p>
                            </div>
                        </section>

                        {/* Article 4 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article4.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article4.para1")}
                                </p>
                                <ul className="list-disc pl-6 space-y-2">
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
                            </div>
                        </section>

                        {/* Article 5 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article5.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article5.para1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article5.para2")}
                                </p>
                            </div>
                        </section>

                        {/* Article 6 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article6.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article6.para1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article6.para2")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article6.para3")}
                                </p>
                            </div>
                        </section>

                        {/* Article 7 - avec emails liés */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article7.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: t(
                                                "legal.article7.para1_html"
                                            ),
                                        }}
                                    />
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article7.para2")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article7.para3")}
                                </p>
                                <p className="leading-relaxed">
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: t(
                                                "legal.article7.para4_html"
                                            ),
                                        }}
                                    />
                                </p>
                            </div>
                        </section>

                        {/* Article 8 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article8.title")}
                            </h2>
                            <div className="space-y-4 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article8.para1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article8.para2")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article8.para3")}
                                </p>

                                <div className="pl-4 border-l-2 border-slate-200 space-y-3 py-2">
                                    <p className="leading-relaxed font-medium">
                                        {t("legal.article8.browser_settings")}
                                    </p>
                                    <p className="leading-relaxed">
                                        {t("legal.article8.ie")}
                                    </p>
                                    <p className="leading-relaxed">
                                        {t("legal.article8.firefox")}
                                    </p>
                                    <p className="leading-relaxed">
                                        {t("legal.article8.safari")}
                                    </p>
                                    <p className="leading-relaxed">
                                        {t("legal.article8.chrome")}
                                    </p>
                                </div>
                            </div>
                        </section>

                        {/* Article 9 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article9.title")}
                            </h2>
                            <p className="text-slate-600 leading-relaxed">
                                {t("legal.article9.content")}
                            </p>
                        </section>

                        {/* Article 10 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article10.title")}
                            </h2>
                            <div className="space-y-2 text-slate-600">
                                <p className="leading-relaxed">
                                    {t("legal.article10.law1")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article10.law2")}
                                </p>
                                <p className="leading-relaxed">
                                    {t("legal.article10.law3")}
                                </p>
                            </div>
                        </section>

                        {/* Article 11 */}
                        <section>
                            <h2 className="text-xl font-semibold text-slate-900 mb-4">
                                {t("legal.article11.title")}
                            </h2>
                            <div className="space-y-3 text-slate-600">
                                <p className="leading-relaxed">
                                    <strong>
                                        {t("legal.article11.user_label")}
                                    </strong>{" "}
                                    {t("legal.article11.user")}
                                </p>
                                <p className="leading-relaxed">
                                    <strong>
                                        {t(
                                            "legal.article11.personal_info_label"
                                        )}
                                    </strong>{" "}
                                    {t("legal.article11.personal_info")}
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

export default LegalNotice;
