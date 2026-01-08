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

const imageExtensions = ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.bmp'];
const soundExtensions = ['.wav', '.mp3', '.ogg', '.m4a', '.aac'];

const isImageUrl = (url) => imageExtensions.some(ext => url.toLowerCase().endsWith(ext));
const isSoundUrl = (url) => soundExtensions.some(ext => url.toLowerCase().endsWith(ext));

// Fonction pour mélanger un tableau avec une seed ou aléatoirement
export const shuffleWithSeed = (array, defaultSeed, isRandom = false) => {
    // Utiliser une seed aléatoire si isRandom est true, sinon utiliser la seed par défaut
    let seed = isRandom ? getRandomSeed() : defaultSeed;
    
    // Créer deux tableaux séparés pour les images et les sons
    const images = array.filter(item => isImageUrl(item.url));
    const sounds = array.filter(item => isSoundUrl(item.url));
    
    // Ajouter les indices d'affichage (P1, P2, etc. pour les images et S1, S2, etc. pour les sons)
    const imagesWithIndices = images.map((item, idx) => ({
        ...item,
        originalIndex: idx,
        displayIndex: idx,
        type: 'image'
    }));
    
    const soundsWithIndices = sounds.map((item, idx) => ({
        ...item,
        originalIndex: images.length + idx,
        displayIndex: idx,
        type: 'sound'
    }));
    
    // Mélanger séparément les images et les sons pour maintenir la cohérence
    const shuffledImages = [...imagesWithIndices];
    const shuffledSounds = [...soundsWithIndices];

    // Mélanger les images
    for (let i = shuffledImages.length - 1; i > 0; i--) {
        const rand = seededRandom(seed + i);
        const j = Math.floor(rand * (i + 1));
        [shuffledImages[i], shuffledImages[j]] = [shuffledImages[j], shuffledImages[i]];
        seed = Math.floor(rand * PRIME);
    }

    // Mélanger les sons
    for (let i = shuffledSounds.length - 1; i > 0; i--) {
        const rand = seededRandom(seed + i);
        const j = Math.floor(rand * (i + 1));
        [shuffledSounds[i], shuffledSounds[j]] = [shuffledSounds[j], shuffledSounds[i]];
        seed = Math.floor(rand * PRIME);
    }

    // Combiner les deux tableaux mélangés séparément
    return [...shuffledImages, ...shuffledSounds];
};