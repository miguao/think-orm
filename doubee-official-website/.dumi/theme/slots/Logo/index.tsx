import { memo } from 'react';
import { Link } from 'dumi';
import { createStyles } from 'antd-style';

const useStyles = createStyles(({ css, token }) => ({
  logo: css`
    display: inline-flex;
    align-items: center;
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
    color: ${token.colorText};
    text-decoration: none;
    transition: color 0.3s;
    letter-spacing: -0.5px;

    &:hover {
      color: ${token.colorPrimary};
    }
  `,
}));

const Logo = memo(() => {
  const { styles } = useStyles();

  return (
    <Link className={styles.logo} to="/">
      LOGO
    </Link>
  );
});

Logo.displayName = 'Logo';

export default Logo;

