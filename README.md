# Cloudflare 域名批量解析工具

一个简单高效的Cloudflare域名批量解析工具，支持快速配置多个域名的DNS记录。

## ✨ 功能特点

- 🚀 批量添加域名到Cloudflare
- 📝 批量配置DNS解析记录（A记录、CNAME等）
- 🔒 一键启用HTTPS
- 🗑️ 批量删除域名及其解析记录
- 💡 简洁直观的用户界面
- 🔐 安全的API认证管理

## 🛠️ 技术栈

- 前端：HTML5, TailwindCSS, JavaScript
- 后端：PHP
- API：Cloudflare API v4

## 📦 安装部署

1. 克隆仓库：
```bash
git clone https://github.com/yourusername/cf-dns-tool.git
```

2. 部署到Web服务器：
   - 确保服务器已安装PHP 7.0+
   - 将所有文件复制到Web根目录
   - 确保PHP有执行权限

## 🚀 使用方法

1. 访问工具网页
2. 点击右上角"设置认证信息"，输入您的Cloudflare Email和API Key
3. 在域名列表中输入要配置的域名（每行一个）
4. 在解析记录中输入DNS记录（格式：name,type,content）
5. 选择是否启用HTTPS
6. 点击"添加配置"或"删除配置"按钮执行操作

### 示例配置

域名列表：
```
example.com
example.org
```

解析记录：
```
www,A,1.2.3.4
@,A,2.3.4.5
blog,CNAME,myblog.com
```

## 🔐 安全说明

- API Key和Email仅保存在浏览器本地存储中
- 所有API请求使用HTTPS加密传输
- 建议使用有限权限的API Token而不是Global API Key

## 📄 开源协议

[MIT License](LICENSE)

## 🤝 贡献指南

欢迎提交Issue和Pull Request来改进这个工具。

## 🙏 致谢

- [Tailwind CSS](https://tailwindcss.com/)
- [Font Awesome](https://fontawesome.com/)
- [Cloudflare API](https://api.cloudflare.com/) 