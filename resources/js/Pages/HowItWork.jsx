import React from "react";
import { Construction, Hammer, Wrench, AlertCircle } from "lucide-react";

function HowItWork() {
    return (
        <div className="min-h-[80vh] bg-gradient-to-b from-white to-gray-50 flex flex-col items-center justify-center px-4">
            <div className="max-w-2xl mx-auto text-center">
                {/* Icône animée */}
                <div className="relative mb-8 inline-flex">
                    <Construction
                        size={64}
                        className="text-blue-600 animate-bounce"
                    />
                    <Wrench
                        size={32}
                        className="absolute -top-2 -right-2 text-yellow-500 animate-pulse"
                    />
                    <Hammer
                        size={32}
                        className="absolute -bottom-2 -left-2 text-blue-500 animate-pulse"
                    />
                </div>

                {/* Titre principal */}
                <h1 className="text-4xl font-bold text-gray-900 mb-4">
                    Page en construction
                </h1>

                {/* Description */}
                {/* <p className="text-lg text-gray-600 mb-8">
                    Notre équipe travaille actuellement sur cette page pour vous
                    offrir une meilleure expérience. Revenez bientôt !
                </p> */}

                {/* Badge informatif */}
                <div className="inline-flex items-center gap-2 bg-yellow-50 text-yellow-800 px-4 py-2 rounded-full mb-8">
                    <AlertCircle size={20} />
                    <span className="text-sm font-medium">
                        Disponible prochainement
                    </span>
                </div>
            </div>
        </div>
    );
}

export default HowItWork;
