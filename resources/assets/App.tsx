import Content from '@/layout/Content/Content';
import MapViewer from '@/layout/Content/MapViewer/MapViewer';
import Footer from '@/layout/Footer/Footer';
import Topbar from '@/layout/Topbar/Topbar';
import React, { Fragment, useEffect } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import MainContainer from './layout/Content/MainContainer/MainContainer';
import Modals from './modals/Modals';
import appStore from './store/AppStore';
import userStore from './store/UserStore';

const App: React.FC = () => {
    useEffect(() => {
        try {
            userStore.getUser();
            appStore.fetchData();
        } catch (_e) {
            console.log(_e);
            // Ignore error
        }
    }, []);

    return (
        <Router>
            <Fragment>
                <Topbar />
                <Content>
                    <MainContainer/>
                    <MapViewer />
                </Content>
                <Footer />
            </Fragment>
            <Modals/>
        </Router>
    );
};

export default App;