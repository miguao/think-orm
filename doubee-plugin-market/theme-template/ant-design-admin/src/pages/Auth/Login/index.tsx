import { login } from "@/services/auth";
import { App, Button } from "antd";

export default () => {
  const { message } = App.useApp();
  function onSubmit() {
    message.loading("请求中..");

    login({
      email: "admin@nanoa.cn",
      password: "123456",
    }).then((response) => {
      console.log(response);
    });

    message.success("登录成功");
  }

  return (
    <div className="login">
      <h1>验证_登录</h1>

      <Button onClick={onSubmit}>点击登录</Button>
    </div>
  );
};
