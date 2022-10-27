import { Node, mergeAttributes } from "@tiptap/core";

export const FilamentBlock = Node.create({
    name: "filament_block",
    group: "block",
    atom: true,
    draggable: true,
    addOptions() {
        return {
            HTMLAttributes: {
                class: "filament-block",
            },
        };
    },
    addAttributes() {
        return {
            type: {
                default: null,
            },
            data: {
                default: null,
            },
            html: {
                default: null,
            },
        };
    },
    parseHTML() {
        return [
            {
                tag: "filament-block",
            },
        ];
    },
    renderHTML({ node }) {
        return ["filament-block", this.options.HTMLAttributes];
    },
    addNodeView() {
        return ({ editor, node, getPos, HTMLAttributes, decorations, extension }) => {
            console.log(node.attrs);
            const dom = document.createElement("div");
            const content = document.createElement("div");
            dom.classList.add("filament-block");
            dom.contentEditable = false;
            dom.innerHTML = node.attrs?.html;

            return {
                dom,
                contentDOM: content,
            };
        };
    },
    addCommands() {
        return {
            setBlock:
                (attributes) =>
                    ({ tr, dispatch }) => {
                        console.log(attributes);
                        const { selection } = tr;
                        const node = this.type.create({ type: attributes.type, data: attributes.data, html: attributes.html });

                        if (dispatch) {
                            tr.replaceRangeWith(selection.from, selection.to, node);
                        }

                        return true;
                    },
        };
    },
});
