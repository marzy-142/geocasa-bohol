<template>
    <component :is="component" v-bind="componentProps" :class="buttonClasses">
        <div
            v-if="loading"
            class="animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-2"
        ></div>
        <component
            v-else-if="icon && iconPosition === 'left'"
            :is="icon"
            class="w-4 h-4 mr-2"
        />

        <slot />

        <component
            v-if="icon && iconPosition === 'right'"
            :is="icon"
            class="w-4 h-4 ml-2"
        />
    </component>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { getVariantClasses } from "@/DesignSystem";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (value) =>
            ["primary", "secondary", "outline", "ghost", "danger"].includes(
                value
            ),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["xs", "sm", "md", "lg", "xl"].includes(value),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icon: [Object, Function],
    iconPosition: {
        type: String,
        default: "left",
        validator: (value) => ["left", "right"].includes(value),
    },
    href: {
        type: String,
        default: null,
    },
    as: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "button",
    },
});

const buttonClasses = computed(() => {
    const baseClasses = getVariantClasses("button", props.variant, props.size);
    const disabledClasses =
        props.loading || props.disabled
            ? "opacity-50 cursor-not-allowed"
            : "cursor-pointer";

    return `${baseClasses} ${disabledClasses}`;
});

const component = computed(() => {
    if (props.as) return props.as;
    if (props.href) return Link;
    return "button";
});

const componentProps = computed(() => {
    const baseProps = {
        disabled: props.loading || props.disabled,
    };

    if (props.href) {
        return {
            ...baseProps,
            href: props.href,
        };
    }

    if (props.as === "button") {
        return {
            ...baseProps,
            type: props.type,
        };
    }

    return baseProps;
});
</script>

