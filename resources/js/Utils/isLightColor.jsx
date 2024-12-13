const isLightColor = (hexColor) => {
    // Enlever le # si présent
    const hex = hexColor.replace('#', '');

    // Convertir en RGB
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    // Calculer la luminosité
    // Formule: (0.299*R + 0.587*G + 0.114*B) / 255
    const brightness = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    // Si la luminosité est > 0.5, c'est une couleur claire
    return brightness > 0.5;
};

export default isLightColor;
