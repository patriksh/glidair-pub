<template>
    <ion-card-header :style="`--after-width: calc(${done + 1} / 4 * 100%)`">
        <div class="steps">
            <div class="left">
                <span v-for="step in done" :key="step" class="step"><ion-icon :icon="checkmarkOutline"></ion-icon></span>
                <span class="step">{{ done + 1 }}</span>
                <span class="title">{{ title }}</span>
            </div>
            <div class="right">
                <span v-for="(step, index) in remaining" :key="index" class="step">{{ done + index + 2 }}</span>
            </div>
        </div>
    </ion-card-header>
</template>

<script>
import { IonCardHeader, IonIcon } from '@ionic/vue';
import { checkmarkOutline } from 'ionicons/icons';
import { useRoute } from 'vue-router';

export default {
    name: 'CompetitionFormHeader',
    components: { IonCardHeader, IonIcon },
    setup() {
        const route = useRoute();

        const step = route.meta.step;
        const steps = ['general', 'judges', 'participants', 'results'];
        const stepTitles = { 'general': 'Osnovno', 'judges': 'Suci', 'participants': 'Natjecatelji', 'results': 'Rezultati' };

        const title = stepTitles[step];
        const done = steps.indexOf(step);

        // Preostali koraci su svi nakon trenutnog, bez njega.
        const remaining = steps.splice(steps.indexOf(step)).filter(s => s != step);

        return {
            title,
            done,
            remaining,
            checkmarkOutline
        }
    }
}
</script>

<style lang="scss" scoped>
ion-card-header {
    background: var(--ion-color-light);

    &::after {
        content: "";
        background: var(--ion-color-primary);
        position: absolute;
        bottom: 0;
        left: 0;
        width: var(--after-width);
        height: 2px;
    }
}

.steps {
    display: flex;
    justify-content: space-between;

    .left, .right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .step {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 24px;
        height: 24px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 100%;
        background: var(--ion-card-background, var(--ion-item-background, var(--ion-background-color, #fff)));
        color: var(--ion-color-light-contrast);

        ion-icon {
            --ionicon-stroke-width: 48px;
        }
    }

    .title {
        color: var(--ion-color-light-contrast);
        font-size: 18px;
        font-weight: bold;
    }

    .left {
        .step {
            background: var(--ion-color-primary);
            color: var(--ion-color-primary-contrast);
        }
    }
}

</style>