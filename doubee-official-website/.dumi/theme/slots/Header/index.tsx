import { Header as Head } from '@lobehub/ui';
import { useResponsive } from 'antd-style';
import { memo } from 'react';
import { useSiteStore } from 'dumi-theme-lobehub/dist/store/useSiteStore';
import Burger from 'dumi-theme-lobehub/dist/slots/Header/Burger';
import DiscordButton from 'dumi-theme-lobehub/dist/slots/Header/DiscordButton';
import GithubButton from 'dumi-theme-lobehub/dist/slots/Header/GithubButton';
import LangSwitch from 'dumi-theme-lobehub/dist/slots/Header/LangSwitch';
import Logo from '../Logo';
import Navbar from 'dumi-theme-lobehub/dist/slots/Navbar';
import SearchBar from 'dumi-theme-lobehub/dist/slots/SearchBar';

const Header = memo(() => {
  const hasHeader = useSiteStore((s) => Boolean(s.routeMeta.frontmatter));
  const { mobile } = useResponsive();

  if (!hasHeader) return null;

  return (
    <Head
      actions={
        mobile ? null : (
          <>
            <SearchBar />
            <DiscordButton />
            <LangSwitch />
            {/* ThemeSwitch 已移除 */}
            <GithubButton />
          </>
        )
      }
      logo={<Logo />}
      nav={mobile ? <Burger /> : <Navbar />}
    />
  );
});

export default Header;

