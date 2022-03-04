
import addCommentToEvent from '@/api/addCommentToEvent';
import extractError from '@/api/extractError';
import getEvents, { IEvent } from '@/api/getEvents';
import joinEvent from '@/api/joinEvent';
import leaveEvent from '@/api/leaveEvent';
import Avatar from '@/components/Avatar/Avatar';
import Comments from '@/components/Comments/Comments';
import { OrangeButton } from '@/components/Form/Button/Button';
import Icon from '@/components/Icon/Icon';
import Link from '@/components/Link/Link';
import ProgressBar from '@/components/ProgressBar/ProgressBar';
import StackPanel from '@/components/StackPanel/StackPanel';
import StarRatings from '@/components/StarRating/StarRating';
import CalendarSvg from '@/static/icons/calendar.svg';
import FacebookSvg from '@/static/icons/facebook.svg';
import LocationSvg from '@/static/icons/location.svg';
import MailSvg from '@/static/icons/mail.svg';
import UserSvg from '@/static/icons/my-account.svg';
import TelephoneSvg from '@/static/icons/telephone.svg';
import noProfile from '@/static/images/noProfile.png';
import mapViewerStore from '@/store/MapViewerStore';
import userStore from '@/store/UserStore';
import View from '@/views/View/View';
import { AxiosError } from 'axios';
import { observer } from 'mobx-react';
import React, { Fragment, useEffect, useState } from 'react';
import { AiOutlineClose } from 'react-icons/ai';
import { useNavigate, useParams } from 'react-router-dom';
import ReactTooltip from 'react-tooltip';
import styles from './EventDetailsView.scss';
import BallSvg from '@/static/icons/ball.svg';
import moment from 'moment';

const EventDetailsView: React.FC = () => {
    const { id } = useParams();
    const [event, setEvent] = useState<IEvent>(null);
    const isEventLoaded = event !== null;
    const userHasAccess = event?.partner?.itsMe;
    const user = userStore.user;
    const [error, setError] = useState<string>('');

    const navigateTo = useNavigate();

    const capitalize = (value: string) => {
        return value[0].toUpperCase() + value.substring(1);
    };

    useEffect(() => {
        mapViewerStore.reset();
        getEvents({ id: +id }).then(events => {
            const event = events[0];
            setEvent(event);

            mapViewerStore.setEventPins([{
                id: event.id,
                color: event.sport.color,
                ...event.facility?.location
            }]);

            if (event.facility?.location) {
                const { lat, lng } = event.facility?.location;

                mapViewerStore.setPosition({lat, lng}, 16);
            }
        });
    }, []);

    const alreadyJoined = !event?.participants?.every(p => p.itsMe == false);
    let signInLink = false;

    const ParticipantsList = () => {
        return (
            <div className={styles.participantsListWrapper}>
                <div className={styles.participantsList}>
                    {
                        [...Array(+event.availableTicketsCount).keys()].map(i => {
                            const participant = event.participants?.[i];

                            const tip = participant?.itsMe ? 'Opuść wydarzenie' :
                                `Usuń użytkownika <b>${participant?.fullName}</b>`;

                            if (participant) {
                                return <div className={styles.participantCell} key={i}>
                                    <Avatar radius={'4px'} size={30} src={participant.avatarUrl ?? noProfile} />
                                    &nbsp;
                                    &nbsp;
                                    {i + 1}.
                                    &nbsp;
                                    {participant.fullName}
                                    {(userHasAccess || participant.itsMe) && (
                                        <div
                                            className={styles.deletUserIcon}
                                            data-delay-show="300"
                                            data-tip={tip}
                                            onClick={async () => {
                                                try {
                                                    await leaveEvent({
                                                        userId: participant.id,
                                                        announcementId: event.id,
                                                        announcementSeatId: participant.seatId
                                                    });

                                                    setEvent(event => {
                                                        event.participants = event.participants.filter(p => {
                                                            return p.id !== participant.id;
                                                        });

                                                        event.soldTicketsCount--;

                                                        return { ...event };
                                                    });

                                                    ReactTooltip.rebuild();
                                                    ReactTooltip.hide();
                                                } catch (err) {
                                                    setError(extractError(err as AxiosError).message);
                                                }
                                            }}
                                        >
                                            <AiOutlineClose />
                                        </div>
                                    )}
                                </div>;
                            } else {
                                if (alreadyJoined || signInLink) {
                                    return <div className={styles.participantCell} key={i}>
                                        <span>{i + 1}.&nbsp;Wolne miejsce</span>
                                    </div>;
                                } else {
                                    signInLink = true;

                                    return (
                                        <div
                                            key={i}
                                            className={styles.participantCell + ' ' + styles.signUpCell}
                                            onClick={async () => {
                                                try {
                                                    await joinEvent({
                                                        announcementId: event.id,
                                                        announcementSeatId: event.seats[0].id
                                                    });

                                                    setEvent(event => {
                                                        event.participants.push({
                                                            id: user.id,
                                                            fullName: user.firstName + ' ' + user.lastName,
                                                            avatarUrl: user.avatarUrl,
                                                            itsMe: true,
                                                            seatId: event.seats[0].id,
                                                        });

                                                        event.soldTicketsCount++;

                                                        return { ...event };
                                                    });

                                                    ReactTooltip.rebuild();
                                                    // ReactTooltip.hide();
                                                } catch (err) {
                                                    setError(extractError(err as AxiosError).message);
                                                }
                                            }}
                                        >
                                            <span>{i + 1}.&nbsp;<span className={styles.signUp}>Zapisz się</span></span>
                                        </div>
                                    );
                                }
                            }
                        })
                    }
                </div>
            </div>
        );
    };

    return (
        <Fragment>
            <View isLoaderVisible={!isEventLoaded}>
                {isEventLoaded && (
                    <Fragment>
                        <header className={styles.header}>
                            {event.imageUrl && <img className={styles.backgroundImage} src={event.imageUrl} alt="" />}
                            <div className={styles.gradientOverlay}></div>
                            <div className={styles.userData}>
                                <img className={styles.userImage} src={event.partner.imageUrl ?? noProfile} alt="" />
                                <div className={styles.userDetails}>
                                    <div className={styles.userDetailsRow}>
                                        <h1>Organizator:</h1>
                                        <Icon svg={UserSvg}>{event.partner.fullName}</Icon>
                                        {/* <StarRatings rating={event.partner.avarageRating} /> */}
                                    </div>

                                    <div className={styles.userDetailsRow + ' ' + styles.contact}>
                                        <h1>Kontakt:</h1>
                                        <Icon svg={TelephoneSvg}>{event.partner.telephone ?? 'Nie podano'}</Icon>
                                        <Icon svg={MailSvg}>{event.partner.contactEmail ?? 'Nie podano'}</Icon>
                                        <Icon svg={FacebookSvg}>
                                            {event.partner.facebook ? (
                                                <Link href="https://www.facebook.com/groups/356092872309341">
                                                    {event.partner.facebook}
                                                </Link>
                                            ) : (
                                                'Nie podano'
                                            )}
                                        </Icon>
                                    </div>
                                </div>
                            </div>
                            <div className={styles.icons}>
                                <h3>
                                    <span style={{ color: '#a1a1a1' }}>Cena:</span> {(event.ticketPrice / 100).toFixed(2)} zł
                                </h3>
                                {event.minSkillLevel && (
                                    <div className={styles.levelIcon}>
                                        <div className={styles.circle}>
                                            {event.minSkillLevel}
                                        </div>
                                    </div>
                                )}
                                <div className={styles.icon} style={{ backgroundColor: event.sport.color.hex() }}>
                                    <BallSvg />
                                </div>
                            </div>
                            {userHasAccess && (
                                <OrangeButton
                                    style={{
                                        position: 'absolute',
                                        left: '20px',
                                        top: '20px',
                                        zIndex: '999'
                                    }}
                                    onClick={() => navigateTo(`/ogloszenia/edytuj/${event.id}`)}
                                >
                                    Edytuj ogłoszenie
                                </OrangeButton>
                            )}
                        </header>
                        <section className={styles.userDetailsRow + ' ' + styles.contactSM}>
                            <h1>Kontakt:</h1>
                            <Icon svg={TelephoneSvg}>{event.partner.telephone ?? 'Nie podano'}</Icon>
                            <Icon svg={MailSvg}>{event.partner.contactEmail ?? 'Nie podano'}</Icon>
                            <Icon svg={FacebookSvg}>
                                {event.partner.facebook ? (
                                    <Link href="https://www.facebook.com/groups/356092872309341">
                                        {event.partner.facebook}
                                    </Link>
                                ) : (
                                    'Nie podano'
                                )}
                            </Icon>
                        </section>
                        <div className={styles.separator}></div>
                        <section>
                            <h1>Lista zapisanych:</h1>
                            <StackPanel padding="20px 0 20px 0" vertical>
                                <StackPanel>
                                    <Icon svg={LocationSvg}>
                                        {event.facility?.name && (
                                            <Fragment>
                                                <b>{event.facility?.name}</b>,&nbsp;
                                            </Fragment>
                                        )}
                                        {capitalize(event.facility?.city.name)}&nbsp;ul.&nbsp;{capitalize(event.facility?.street)}
                                    </Icon>
                                    <Icon svg={CalendarSvg}>
                                        {moment(event.startDate).format('DD.MM.YY hh:mm')} - {moment(event.endDate).format('DD.MM.YY hh:mm')}
                                    </Icon>
                                </StackPanel>
                            </StackPanel>
                            <div style={{ width: '300px', display: 'flex', alignItems: 'center', gap: '10px' }}>
                                <ProgressBar progress={event.soldTicketsCount / event.availableTicketsCount * 100} />
                                <div>
                                    {event.soldTicketsCount}/{event.availableTicketsCount}
                                </div>
                            </div>
                            <ParticipantsList />
                        </section>
                        <div className={styles.separator}></div>
                        <section>
                            <h1>Opis:</h1>
                            <div className={styles.description}>
                                {event.description ?? 'To wydarzenie nie posiada opisu'}
                            </div>
                        </section>
                        <div className={styles.separator}></div>
                        <section>
                            <h1>Komentarze:</h1>
                            <Comments
                                comments={event.comments ?? []}
                                onAddComment={async (comment) => {
                                    try {
                                        await addCommentToEvent({
                                            announcementId: event.id,
                                            comment
                                        });

                                        setEvent(event => {
                                            event.comments.push({
                                                username: user.firstName + ' ' + user.lastName,
                                                comment: comment,
                                                createdAt: new Date().toLocaleDateString(),
                                                userAvatarUrl: user.avatarUrl
                                            });

                                            return { ...event };
                                        });
                                    } catch (err) {
                                        setError(extractError(err as AxiosError).message);
                                    }
                                }}
                            />
                        </section>
                    </Fragment>
                )}
            </View >
            {isEventLoaded && <ReactTooltip multiline={true} html={true} className={styles.tooltip} />}
        </Fragment>
    );
};

export default observer(EventDetailsView);