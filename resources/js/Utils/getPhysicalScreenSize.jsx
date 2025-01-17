const getPhysicalScreenSize = () => {
    // On crée un div de 1 pouce CSS
    const div = document.createElement("div");
    div.style.width = "1in";
    div.style.height = "1in";
    div.style.position = "fixed";
    div.style.left = "-100%"; // Hors écran
    document.body.appendChild(div);

    // On obtient la taille réelle en pixels d'un pouce CSS
    const actualPixelsPerInch = div.offsetWidth;

    // On nettoie
    document.body.removeChild(div);

    // Fonction de conversion
    const pixelsToCentimeters = (pixels) => {
        const inchesPerCm = 2.54;
        return (pixels / actualPixelsPerInch) * inchesPerCm;
    };

    // Pour le debug
    console.log(`Actual PPI: ${actualPixelsPerInch}`);

    return { pixelsToCentimeters };
};

export default getPhysicalScreenSize;
