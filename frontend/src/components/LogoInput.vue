<template>
    <input ref="logoInput" @change="onLogoChange" type="file" accept="image/*" v-show="false" />
    <ion-item @click="logoInput.click();" button="true" lines="none" :disabled="loading" class="logo">
        <ion-label v-if="!logoPreview">
            <ion-icon :icon="imageOutline"></ion-icon>
        </ion-label>
        <img v-else :src="logoPreview">
    </ion-item>
    <hr>
</template>

<script>
import { IonItem, IonLabel, IonIcon } from '@ionic/vue';
import { imageOutline } from 'ionicons/icons';
import { computed, ref } from 'vue';
import { competitionLogoUrl } from '@/helpers/competition';

export default {
    name: 'LogoInput',
    components: { IonItem, IonLabel, IonIcon },
    props: ['loading', 'modelValue'],
    setup(props, { emit }) {
        const logo = computed({
            get() {
                return props.modelValue;
            },
            set(value) {
                emit('update:modelValue', value);
            }
        });

        // Prikaži logo iz inputa ili s postojećeg entiteta.
        const logoInput = ref();
        const logoPreview = computed(() => {
            if(logo.value) {
                if(logo.value instanceof File) {
                    return URL.createObjectURL(logo.value);
                } else {
                    return competitionLogoUrl(logo.value);
                }
            }

            return null;
        });

        const onLogoChange = (event) => {
            if(event.target.files.length) {
                logo.value = event.target.files[0];
            }
        };

        return {
            logoInput,
            logoPreview,
            onLogoChange,
            imageOutline
        };
    }
}
</script>

<style lang="scss" scoped>
.logo {
    text-align: center;
    background: var(--ion-color-light);
    border-radius: 8px;
    --inner-padding-end: 0;
    --padding-start: 0px;
    --padding-end: 0px;

    ion-label {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        padding: 20px;
    }

    img {
        margin: 0 auto;
    }
}
</style>