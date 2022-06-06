<template>
    <master-layout :title="pageTitle">
        <ion-grid class="page" fixed>
            <ion-row>
                <ion-col size="12" size-lg="8">
                    <ion-card v-if="pendingResultUpdate" class="pending">
                        <ion-item lines="none">
                            <ion-spinner name="dots" slot="start"></ion-spinner>
                            <ion-label>Rezultati se spremaju nakon povezivanja s internetom.</ion-label>
                        </ion-item>
                    </ion-card>

                    <ion-card class="competition">
                        <div class="logo">
                            <img v-if="competition?.logo" :src="competitionLogoUrl(competition.logo)">
                            <ion-skeleton-text v-if="!competition" animated style="line-height: 200px; margin: 0; border-radius: 0;"></ion-skeleton-text>
                        </div>
                        <div class="info">
                            <ion-card-header>
                                <ion-card-title>
                                    <template v-if="competition">{{ competition.name }}</template>
                                    <ion-skeleton-text v-else animated style="line-height: 25px;"></ion-skeleton-text>
                                </ion-card-title>
                            </ion-card-header>
                            <ion-card-content>
                                <ion-chip>
                                    <template v-if="competition">
                                        <ion-icon :icon="locationOutline" color="primary"></ion-icon>
                                        <ion-label>{{ competition.location }}</ion-label>
                                    </template>
                                    <ion-skeleton-text v-else animated style="width: 60px; line-height: 16px; border-radius: 100px;"></ion-skeleton-text>
                                </ion-chip>
                                <ion-chip>
                                    <template v-if="competition">
                                        <ion-icon :icon="calendarOutline" color="primary"></ion-icon>
                                        <ion-label>{{ competitionDateFormatted(competition.date) }}</ion-label>
                                    </template>
                                    <ion-skeleton-text v-else animated style="width: 60px; line-height: 16px; border-radius: 100px;"></ion-skeleton-text>
                                </ion-chip>
                                <ion-chip>
                                    <template v-if="competition">
                                        <ion-icon :icon="handRightOutline" color="primary"></ion-icon>
                                        <ion-label>{{ competition?.director }}</ion-label>
                                    </template>
                                    <ion-skeleton-text v-else animated style="width: 60px; line-height: 16px; border-radius: 100px;"></ion-skeleton-text>
                                </ion-chip>
                            </ion-card-content>
                        </div>
                    </ion-card>
                    <CompetitionAdminButtons class="ion-hide-lg-up" :competition="competition" />
                    <ion-card v-if="competition?.participants.length" class="accordion">
                        <ion-accordion-group :value="results ? '' : 'participants'">
                            <ion-accordion value="participants">
                                <ion-item class="header" slot="header">
                                    <ion-card-title>Natjecatelji</ion-card-title>
                                </ion-item>

                                <ion-list slot="content">
                                    <ion-item v-for="participant in competition.participants" :key="participant.id">
                                        <ion-label>
                                            {{ participant.order }}. {{ participant.user.name }}
                                            <div><small>{{ getCountryName(participant.user.country) }}, {{ participant.user.club.name }}</small></div>
                                        </ion-label>
                                    </ion-item>
                                </ion-list>
                            </ion-accordion>
                        </ion-accordion-group>
                    </ion-card>
                    <ion-card v-if="results" class="accordion">
                        <ion-accordion-group value="results">
                            <ion-accordion value="results">
                                <ion-item class="header" slot="header">
                                    <ion-card-title>Rezultati</ion-card-title>
                                </ion-item>

                                <ion-list slot="content" class="results">
                                    <ResultFilters :club-filter="true" @change="filterResults" />
                                    <ion-item v-for="(participant, index) in results" :key="participant.id">
                                        <ion-label>
                                            <div :class="`place place-${index + 1}`">
                                                <span class="outer">
                                                    <span class="inner">{{ index + 1}}</span>
                                                </span>
                                            </div>
                                            <div class="info">
                                                <span class="name">{{ participant.user.name }}</span>
                                                <div class="scores">
                                                    <span v-for="(round, j) in participant.rounds" :key="j" :class="round.ignore ? 'ignore' : ''">
                                                        {{ round.score }}
                                                    </span>
                                                </div>
                                            </div>
                                        </ion-label>
                                    </ion-item>
                                </ion-list>
                            </ion-accordion>
                        </ion-accordion-group>
                    </ion-card>
                    <ion-card class="accordion" v-if="!competition">
                        <ion-accordion-group>
                            <ion-accordion disabled>
                                <ion-item class="header" slot="header">
                                    <ion-card-title>
                                        <ion-skeleton-text style="width: 100px; line-height: 26.2px;"></ion-skeleton-text>
                                    </ion-card-title>
                                </ion-item>
                            </ion-accordion>
                        </ion-accordion-group>
                    </ion-card>
                </ion-col>
                <ion-col size="12" size-lg="4">
                    <CompetitionAdminButtons class="ion-hide-lg-down" :competition="competition" />
                    <ion-card class="accordion judges" v-if="competition?.judges.length">
                        <ion-accordion-group value="judges">
                            <ion-accordion value="judges">
                                <ion-item class="header" slot="header">
                                    <ion-card-title>Suci</ion-card-title>
                                </ion-item>

                                <ion-list slot="content">
                                    <ion-item v-for="judge in competition.judges" :key="judge.id">
                                        <ion-label>{{ judge.name }} ({{ judge.role }})</ion-label>
                                    </ion-item>
                                </ion-list>
                            </ion-accordion>
                        </ion-accordion-group>
                    </ion-card>
                    <ion-card class="accordion judges" v-if="!competition">
                        <ion-accordion-group>
                            <ion-accordion disabled>
                                <ion-item class="header" slot="header">
                                    <ion-card-title>
                                        <ion-skeleton-text style="width: 50px; line-height: 26.2px;"></ion-skeleton-text>
                                    </ion-card-title>
                                </ion-item>
                            </ion-accordion>
                        </ion-accordion-group>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>
        
        <template #toolbar-buttons-end v-if="competition && loggedIn">
            <ion-button @click="openDeletePopover"><ion-icon :icon="ellipsisVerticalOutline"></ion-icon></ion-button>
        </template>
    </master-layout>
</template>

<script>
import { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel, IonGrid, IonRow, IonCol, IonAccordionGroup, IonAccordion, IonList, IonItem, IonButton, IonSpinner, IonSkeletonText, onIonViewWillEnter, toastController, popoverController } from '@ionic/vue';
import { locationOutline, calendarOutline, handRightOutline, ellipsisVerticalOutline } from 'ionicons/icons';
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router';
import store from '@/store';
import { deleteCompetition } from '@/http/competition';
import { competitionNotFound, competitionResultFilter, competitionLogoUrl, competitionDateFormatted } from '@/helpers/competition';
import { isOnline } from '@/helpers/network';
import { getCountryName } from '@/helpers/misc';
import CompetitionAdminButtons from '@/components/CompetitionAdminButtons';
import ResultFilters from '@/components/ResultFilters';
import DeletePopover from '@/components/DeletePopover';

export default {
    name: 'CompetitionViewPage',
    components: { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel, IonGrid, IonRow, IonCol, IonAccordionGroup, IonAccordion, IonList, IonItem, IonButton, IonSpinner, IonSkeletonText, CompetitionAdminButtons, ResultFilters },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const competition = ref();
        const id = parseInt(route.params.id);
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);
        const pendingResultUpdate = computed(() => store.getters['competitions/pendingResultUpdate'](id));
        
        // Učitaj podatke natjecanja sa servera po IDu iz URLa.
        const fetchCompetition = async () => {
            if(isNaN(id)) return competitionNotFound();

            competition.value = await store.dispatch('competitions/fetchSingle', id) || await competitionNotFound();

            // Ako bilo koji natjecatelj ima unesene rezultate, prikaži ih.
            for(const participant of competition.value.participants) {
                if(participant.rounds.length) {
                    // Stvori popis rezultata iz popisa natjecatelja sortiranog po bodovima.
                    competition.value.results = Object.create(competition.value.participants).sort((a, b) => a.score - b.score);
                    break;
                }
            }
        };
        
        onIonViewWillEnter(() => fetchCompetition());

        const filters = ref();
        const filterResults = (f) => filters.value = f;
        const results = computed(() => competitionResultFilter(competition.value?.results, filters.value));

        const pageTitle = computed(() => competition.value?.name ? competition.value.name : 'Natjecanje');

        // Izbriši natjecanje, prikaži poruku i otvori popis svih natjecanja.
        const onDeleteCompetition = async () => {
            if(!await isOnline()) {
                return toastController.create({ message: 'Natjecanje nije moguće izbrisati dok nema internetske veze.', duration: 2000 }).then(t => t.present());
            }

            await deleteCompetition(id);
            store.dispatch('competitions/fetch');

            router.push({ name: 'home' });
            toastController.create({ message: 'Natjecanje je uspješno izbrisano.', duration: 2000 }).then(t => t.present());
        }

        const deletePopover = ref();
        const openDeletePopover = async (event) => {
            deletePopover.value = await popoverController.create({
                component: DeletePopover,
                componentProps: {
                    delete: onDeleteCompetition,
                    text: 'Izbriši natjecanje',
                    message: 'Jeste li sigurni da želite izbrisati ovo natjecanje?'
                },
                event
            }).then(p => p.present());
        }

        return {
            competition,
            results,
            filterResults,
            pageTitle,
            loggedIn,
            competitionLogoUrl,
            competitionDateFormatted,
            getCountryName,
            openDeletePopover,
            pendingResultUpdate,
            locationOutline,
            calendarOutline,
            handRightOutline,
            ellipsisVerticalOutline
        }
    },
}
</script>

<style lang="scss" scoped>
@import '@/theme/results.scss';

.pending {
    ion-spinner {
        transform: scale(0.8);
        margin: 2px 12px 2px -10px;
    }

    ion-label {
        font-size: 12px;
    }
}
.competition {
    display: flex;
    flex-direction: column;

    @media (min-width: 992px) { // Use Ionic breakpoint variables?
        flex-direction: column-reverse;
    }

    ion-card-content {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;

        ion-chip {
            margin-inline: 0;
        }
    }
}

ion-card-title {
    font-size: 22px;
}

.accordion {
    ion-accordion-group {
        padding: 20px;
    }

    ion-accordion {
        background: none;
    }

    ion-item.header {
        --min-height: auto;
        --background-activated: none;

        ion-icon {
            margin: 0;
        }
    }

    ion-list {
        width: 100%;
        padding-top: 20px;
    }

    ion-item {
        --padding-start: 0px;
        --padding-end: 0px;
        --inner-padding-end: 0;
    }

    &.judges {
        @media(max-width: 991px) {
            margin-top: 0px;
        }
    }
}
</style>

<style>
.accordion .ion-accordion-toggle-icon {
    margin: 0;
}
</style>