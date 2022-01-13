import React from 'react';
import styles from './Link.scss';

export interface LinkProps {
    href?: string;
    onClick?: React.MouseEventHandler;
    fixedColor?: boolean;
    fixedUnderline?: boolean;
}

const Link: React.FC<LinkProps> = ({ href, children, onClick, fixedColor = false, fixedUnderline = false }) => {
    const onClickWrapper: React.MouseEventHandler = (e) => {
        e.preventDefault();

        if (href) {
            window.open(href, '_blank');
        } else {
            onClick(e);
        }
    };

    const classes = [
        styles.link
    ];

    fixedColor && classes.push(styles.fixedColor);
    fixedUnderline && classes.push(styles.fixedUnderline);

    return (
        <a
            className={classes.join(' ')}
            onClick={onClickWrapper}
            href={href ? href : undefined}
        >
            {children}
        </a>
    );
};

export default Link;