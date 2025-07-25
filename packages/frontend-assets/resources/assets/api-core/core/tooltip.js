export const Tooltip = (node, content, position) => {
    if(!node || !content) return;
    node = node.isMWElement ? node.get(0) : node;
    node.dataset.tooltip = content;
    node.dataset.mwTitle = content;
    node.dataset.tooltipPosition = position || 'top-center';

};


