import { Node, mergeAttributes } from "@tiptap/core";

export const JumpLink = Node.create({
    name: "jumpLink",

    group: "block",

    content: 'inline*',

    draggable: true,

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: element => ({
                    id: element.querySelector('span').textContent,
                }),
                renderHTML: attributes => ({
                    'id': attributes.id,
                }),
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: `div[data-type="${this.name}"]`,
            }
        ];
    },

    renderHTML({ node, HTMLAttributes}) {
        return [
            'div',
            mergeAttributes({ 'data-type': this.name }, HTMLAttributes),
            ['span', {}, 0]
        ];
    },

    addCommands() {
        return {
            insertJumpLink: () => ({ commands }) => {
                return commands.setNode(this.name);
            },
        };
    },
});
