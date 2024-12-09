// Utilitaires pour le positionnement en grille
const MARGIN_TOP = 50;
const MARGIN_LEFT = 50;
const GRID_SPACING = 20;
const GRID_COLS = 5;

export const calculateGridPosition = (index, itemSize, stageWidth, stageHeight) => {
    const col = index % GRID_COLS;
    const row = Math.floor(index / GRID_COLS);

    const x = MARGIN_LEFT + col * (itemSize + GRID_SPACING);
    const y = MARGIN_TOP + row * (itemSize + GRID_SPACING);

    // Contraindre aux limites de la scène
    return {
        x: Math.min(x, stageWidth - itemSize),
        y: Math.min(y, stageHeight - itemSize)
    };
};

export const arrangeItemsInGrid = (items, size, stageWidth, stageHeight) => {
    return items.map((item, index) => {
        const itemSize = parseInt(item.button_size || size);
        const position = calculateGridPosition(index, itemSize, stageWidth, stageHeight);

        return {
            ...item,
            ...position,
            width: itemSize,
            height: itemSize,
        };
    });
};