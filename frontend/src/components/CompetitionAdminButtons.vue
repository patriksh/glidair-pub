<template>
    <ion-card v-if="loggedIn">
        <ion-grid>
            <ion-row>
                <ion-col size="6" size-lg="12">
                    <template v-if="competition"><ion-button @click="editCompetition" @touchstart="editLongPressStart" @touchend="editLongPressCancel" @touchmove="editLongPressCancel" expand="block">Uredi natjecanje</ion-button></template>
                    <ion-skeleton-text v-else style="line-height: 44.8px;"></ion-skeleton-text>
                </ion-col>
                <ion-col size="6" size-lg="12">
                    <template v-if="competition">
                        <ion-button v-if="competition.results" @click="downloadReport" expand="block">Preuzmi izvještaj</ion-button>
                        <ion-button v-else @click="editCompetitionResults" expand="block">Unesi rezultate</ion-button>
                    </template>
                    <ion-skeleton-text v-else style="line-height: 44.8px;"></ion-skeleton-text>
                </ion-col>
            </ion-row>
        </ion-grid>
    </ion-card>
</template>

<script>
import { IonCard, IonGrid, IonRow, IonCol, IonButton, IonSkeletonText, popoverController } from '@ionic/vue';
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router';
import { Browser } from '@capacitor/browser';
import store from '@/store';
import { downloadCompetitionReportURL } from '@/http/competition';
import CompetitionEditPopover from '@/components/CompetitionEditPopover';

export default {
    name: 'CompetitionAdminButtons',
    components: { IonCard, IonGrid, IonRow, IonCol, IonButton, IonSkeletonText },
    props: ['competition'],
    setup(props) {
        const router = useRouter();
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);
        
        const editCompetition = () => router.push({ name: 'competition-form-general', params: { id: props.competition.id }});
        const editCompetitionResults = () => router.push({ name: 'competition-form-results', params: { id: props.competition.id }});

        // Prikaži dodatne opcije prilikom držanja buttona "Uredi natjecanje".
        const editLongPressInterval = ref();
        const editLongPressPopover = ref();
        const editLongPressStart = (event) => {
            editLongPressInterval.value = setTimeout(async () => {
                editLongPressInterval.value = null;

                editLongPressPopover.value = await popoverController.create({
                    component: CompetitionEditPopover,
                    componentProps: { id: props.competition.id },
                    event
                }).then(p => p.present());
            }, 500);
        };
        const editLongPressCancel = () => clearTimeout(editLongPressInterval.value);

        // Preuzmi izvješće sa servera (potrebna je prijava pa nije moguće samo "kliknuti na link"), ime datoteke je slug generiran iz imena natjecanja.
        const downloadReport = async () => {
            const url = downloadCompetitionReportURL(props.competition.id);
            await Browser.open({ url });
        };

        return {
            loggedIn,
            editCompetition,
            editCompetitionResults,
            editLongPressStart,
            editLongPressCancel,
            downloadReport
        }
    }
}
</script>