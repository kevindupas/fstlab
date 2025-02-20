// Un nombre premier pour améliorer la distribution
const PRIME = 2147483647;

// Un générateur de nombre pseudo-aléatoire basé sur une seed
export const seededRandom = (seed) => {
    const a = 16807;
    const m = PRIME;
    return ((seed * a) % m) / m;
};

// Fonction pour obtenir une seed basée sur un timestamp
const getRandomSeed = () => {
    return Math.floor(Date.now() * Math.random());
};

// Fonction pour mélanger un tableau avec une seed ou aléatoirement
export const shuffleWithSeed = (array, defaultSeed, isRandom = false) => {
    // Utiliser une seed aléatoire si isRandom est true, sinon utiliser la seed par défaut
    let seed = isRandom ? getRandomSeed() : defaultSeed;
    
    // Ajouter l'index original à chaque élément
    const arrayWithIndices = array.map((item, index) => ({
        ...item,
        originalIndex: index
    }));
    
    const shuffled = [...arrayWithIndices];
    
    for (let i = shuffled.length - 1; i > 0; i--) {
        const rand = seededRandom(seed + i);
        const j = Math.floor(rand * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        seed = Math.floor(rand * PRIME);
    }
    
    return shuffled;
};