<template>
    <master-layout title="Rezultati">
        <PullToRefresh :refresh="refresh" />

        <ListContainer>
            <div class="segments">
                <ion-segment @ionChange="onSegmentChange">
                    <ion-segment-button value="participants" ref="defaultSegmentButton">
                        <ion-label>Natjecatelji</ion-label>
                    </ion-segment-button>
                    <ion-segment-button value="clubs">
                        <ion-label>Klubovi</ion-label>
                    </ion-segment-button>
                </ion-segment>
            </div>

            <ResultFilters v-show="tab == 'participants'" :year-filter="true" :club-filter="true" @change="filters => participantFilters = filters" @year-change="year => fetchParticipants(year)" />
            <ResultFilters v-show="tab == 'clubs'" :year-filter="true" @change="filters => clubFilters = filters" @year-change="year => fetchClubs(year)" />
            
            <template v-if="tab == 'participants'">
                <Loading v-if="participantsLoading" height="70%" />
                <transition name="slide-fade">
                    <ion-card v-if="!participantsLoading">
                        <ion-card-header>
                            <ion-card-title>Poredak natjecatelja</ion-card-title>
                        </ion-card-header>
                        <ion-card-content>
                            <ion-list slot="content" class="results" v-if="participants?.length">
                                <ion-accordion-group>
                                    <ion-accordion v-for="(participant, index) in participants" :key="participant.id" :value="`participant-${index}`">
                                        <ion-item slot="header">
                                            <ion-label>
                                                <div :class="`place place-${index + 1}`">
                                                    <span class="outer">
                                                        <span class="inner">{{ index + 1}}</span>
                                                    </span>
                                                </div>
                                                <div class="info">
                                                    <span class="name">{{ participant.user.name }}</span>
                                                    <span class="score">{{ participant.score }}</span>
                                                </div>
                                            </ion-label>
                                        </ion-item>
                                        <ion-list slot="content">
                                            <ion-item v-for="competition in participant.competitions" :key="competition.id" class="competitions">
                                                <div class="name">{{ competition.name }}</div>
                                                <div class="scores">
                                                    <span v-for="(round, j) in competition.participant.rounds" :key="j" :class="round.ignore ? 'ignore' : ''">
                                                        {{ round.score }}
                                                    </span>
                                                </div>
                                            </ion-item>
                                        </ion-list>
                                    </ion-accordion>
                                </ion-accordion-group>
                            </ion-list>
                        </ion-card-content>
                    </ion-card>
                </transition>
            </template>

            <template v-if="tab == 'clubs'">
                <Loading v-if="clubsLoading" height="70%" />
                <transition name="slide-fade">
                    <ion-card v-if="!clubsLoading">
                        <ion-card-header>
                            <ion-card-title>Poredak klubova</ion-card-title>
                        </ion-card-header>
                        <ion-card-content>
                            <ion-list slot="content" class="results" v-if="clubs?.length">
                                <ion-accordion-group>
                                    <ion-accordion v-for="(club, index) in clubs" :key="club.id" :value="`club-${index}`">
                                        <ion-item slot="header">
                                            <ion-label>
                                                <div :class="`place place-${index + 1}`">
                                                    <span class="outer">
                                                        <span class="inner">{{ index + 1}}</span>
                                                    </span>
                                                </div>
                                                <div class="info">
                                                    <span class="name">{{ club.club.name }}</span>
                                                    <span class="score">{{ club.score }}</span>
                                                </div>
                                            </ion-label>
                                        </ion-item>
                                        <ion-list slot="content">
                                            <ion-item v-for="competition in club.competitions" :key="competition.id" class="competitions">
                                                <div class="name">{{ competition.name }}</div>
                                                <div class="score">{{ competition.score }}</div>
                                            </ion-item>
                                        </ion-list>
                                    </ion-accordion>
                                </ion-accordion-group>
                            </ion-list>
                        </ion-card-content>
                    </ion-card>
                </transition>
            </template>
        </ListContainer>
    </master-layout>
</template>

<script>
import { IonSegment, IonSegmentButton, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonList, IonItem, IonLabel, IonAccordion, IonAccordionGroup } from '@ionic/vue';
import { ref, computed, onMounted } from 'vue';
import store from '@/store';
import { competitionResultFilter } from '@/helpers/competition';
import { clubResultFilter } from '@/helpers/club';
import PullToRefresh from '@/components/PullToRefresh';
import ListContainer from '@/components/ListContainer';
import ResultFilters from '@/components/ResultFilters';
import Loading from '@/components/Loading';

export default {
    name: 'LeaderboardPage',
    components: { IonSegment, IonSegmentButton, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonList, IonItem, IonLabel, IonAccordion, IonAccordionGroup, PullToRefresh, ListContainer, ResultFilters, Loading },
    setup() {
        const tab = ref('participants');
        const participantsLoading = ref(true);
        const clubsLoading = ref(true);

        const participantYear = ref(2021); // new Date.getFullYear();
        const clubYear = ref(2021); // new Date.getFullYear();

        const storeParticipants = computed(() => store.getters['leaderboard/participants'][participantYear.value]);

        // Učitaj rezultate natjecatelja ako nisu prisutni u storeu.
        const fetchParticipants = async (year = null) => {
            if(year) participantYear.value = year;

            participantsLoading.value = true;
            await store.dispatch('leaderboard/fetchParticipants', { year: participantYear.value, toast: true });
            participantsLoading.value = false;
        }

        if(!storeParticipants.value) {
            fetchParticipants();
        }

        const storeClubs = computed(() => store.getters['leaderboard/clubs'][clubYear.value]);

        // Učitaj rezultate natjecatelja ako nisu prisutni u storeu.
        const fetchClubs = async (year = null) => {
            if(year) clubYear.value = year;

            clubsLoading.value = true;
            await store.dispatch('leaderboard/fetchClubs', { year: clubYear.value, toast: true });
            clubsLoading.value = false;
        }

        if(!storeClubs.value) {
            fetchClubs();
        }

        const participantFilters = ref();
        const participants = computed(() => {
            return competitionResultFilter(storeParticipants.value, participantFilters.value)
        });

        const clubFilters = ref();
        const clubs = computed(() => {
            return clubResultFilter(storeClubs.value, clubFilters.value)
        });

        const onSegmentChange = async (event) => tab.value = event.detail.value;

        // Default value on ion-segment causes problems, bug in Ionic?
        const defaultSegmentButton = ref();
        onMounted(() => {
            setTimeout(() => defaultSegmentButton.value.$el.click(), 50);
        });

        const refresh = async (event) => {
            if(tab.value == 'participants') {
                await store.dispatch('leaderboard/fetchParticipants', true);
            } else {
                await store.dispatch('leaderboard/fetchClubs', true);
            }

            event.target.complete();
        }

        return {
            participants,
            participantFilters,
            participantsLoading,
            clubs,
            clubFilters,
            clubsLoading,
            fetchParticipants,
            fetchClubs,
            refresh,
            tab,
            onSegmentChange,
            defaultSegmentButton
        }
    }
}
</script>

<style lang="scss" scoped>
@import '@/theme/results.scss';

.segments {
    margin: 20px 16px;
}

.filters {
    padding: 10px 12px;
}

.results {
    ion-accordion {
        background: none;
    }

    .place {
        .outer {
            width: 32px;
            height: 32px;
        }

        .inner {
            width: 24px;
            height: 24px;
            padding: 2px;
            font-size: 14px;
        }
    }

    .info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex: 1;
    }

    .competitions {
        .name {
            font-size: 16px;
            margin-right: 8px;
        }

        .scores {
            font-size: 12px;
            margin-top: 0;

            span {
                padding: 2px 4px;
                margin-right: 2px;
            }
        }

        .score {
            font-size: 12px;
            padding: 2px 4px;
        }
    }
}
</style>